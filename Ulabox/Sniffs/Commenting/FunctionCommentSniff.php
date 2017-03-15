<?php
/**
 * Parses and verifies the doc comments for functions.
 */
if (class_exists('PEAR_Sniffs_Commenting_FunctionCommentSniff', true) === false) {
    throw new PHP_CodeSniffer_Exception('Class PEAR_Sniffs_Commenting_FunctionCommentSniff not found');
}

/**
 * Parses and verifies the doc comments for functions.
 */
class Ulabox_Sniffs_Commenting_FunctionCommentSniff extends PEAR_Sniffs_Commenting_FunctionCommentSniff
{

    /**
     * An array of variable types for param/var we will check.
     *
     * @var array(string)
     */
    public static $allowedTypes = [
        'array',
        'bool',
        'float',
        'int',
        'mixed',
        'object',
        'string',
        'resource',
        'callable',
    ];


    /**
     * Process the return comment of this function comment.
     *
     * @param PHP_CodeSniffer_File $phpcsFile    The file being scanned.
     * @param int                  $stackPtr     The position of the current token
     *                                           in the stack passed in $tokens.
     * @param int                  $commentStart The position in the stack where the comment started.
     *
     * @return void
     */
    protected function processReturn(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $commentStart)
    {
        $tokens = $phpcsFile->getTokens();
        // Skip constructor and destructor.
        $methodName = $phpcsFile->getDeclarationName($stackPtr);
        $isSpecialMethod = ($methodName === '__construct' || $methodName === '__destruct');
        $return = null;
        foreach ($tokens[$commentStart]['comment_tags'] as $tag) {
            if ($tokens[$tag]['content'] === '@return') {
                if ($return !== null) {
                    $error = 'Only 1 @return tag is allowed in a function comment';
                    $phpcsFile->addError($error, $tag, 'DuplicateReturn');

                    return;
                }
                $return = $tag;
            }
        }
        if ($isSpecialMethod === true) {
            return;
        }
        if ($return !== null) {
            $content = $tokens[($return + 2)]['content'];
            if (empty($content) === true || $tokens[($return + 2)]['code'] !== T_DOC_COMMENT_STRING) {
                $error = 'Return type missing for @return tag in function comment';
                $phpcsFile->addError($error, $return, 'MissingReturnType');
            } else {
                // Check return type (can be multiple, separated by '|').
                $typeNames = explode('|', $content);
                $suggestedNames = [];
                foreach ($typeNames as $i => $typeName) {
                    $suggestedName = $this->suggestType($typeName);
                    if (in_array($suggestedName, $suggestedNames) === false) {
                        $suggestedNames[] = $suggestedName;
                    }
                }
                $suggestedType = implode('|', $suggestedNames);
                if ($content !== $suggestedType) {
                    $error = 'Expected "%s" but found "%s" for function return type';
                    $data = [
                        $suggestedType,
                        $content,
                    ];
                    $fix = $phpcsFile->addFixableError($error, $return, 'InvalidReturn', $data);
                    if ($fix === true) {
                        $phpcsFile->fixer->replaceToken(($return + 2), $suggestedType);
                    }
                }
                // If the return type is void, make sure there is
                // no return statement in the function.
                if ($content === 'void') {
                    if (isset($tokens[$stackPtr]['scope_closer']) === true) {
                        $endToken = $tokens[$stackPtr]['scope_closer'];
                        for ($returnToken = $stackPtr; $returnToken < $endToken; $returnToken++) {
                            if ($tokens[$returnToken]['code'] === T_CLOSURE) {
                                $returnToken = $tokens[$returnToken]['scope_closer'];
                                continue;
                            }
                            if ($tokens[$returnToken]['code'] === T_RETURN
                                || $tokens[$returnToken]['code'] === T_YIELD
                            ) {
                                break;
                            }
                        }
                        if ($returnToken !== $endToken) {
                            // If the function is not returning anything, just
                            // exiting, then there is no problem.
                            $semicolon = $phpcsFile->findNext(T_WHITESPACE, ($returnToken + 1), null, true);
                            if ($tokens[$semicolon]['code'] !== T_SEMICOLON) {
                                $error = 'Function return type is void, but function contains return statement';
                                $phpcsFile->addError($error, $return, 'InvalidReturnVoid');
                            }
                        }
                    }
                } else if ($content !== 'mixed') {
                    // If return type is not void, there needs to be a return statement
                    // somewhere in the function that returns something.
                    if (isset($tokens[$stackPtr]['scope_closer']) === true) {
                        $endToken = $tokens[$stackPtr]['scope_closer'];
                        $returnToken = $phpcsFile->findNext([T_RETURN, T_YIELD], $stackPtr, $endToken);
                        if ($returnToken === false) {
                            $error = 'Function return type is not void, but function has no return statement';
                            $phpcsFile->addError($error, $return, 'InvalidNoReturn');
                        } else {
                            $semicolon = $phpcsFile->findNext(T_WHITESPACE, ($returnToken + 1), null, true);
                            if ($tokens[$semicolon]['code'] === T_SEMICOLON) {
                                $error = 'Function return type is not void, but function is returning void here';
                                $phpcsFile->addError($error, $returnToken, 'InvalidReturnNotVoid');
                            }
                        }
                    }
                }
            }
        } else {
            if (isset($tokens[$stackPtr]['scope_closer']) === true) {
                $endToken = $tokens[$stackPtr]['scope_closer'];
                $returnToken = $phpcsFile->findNext([T_RETURN, T_YIELD], $stackPtr, $endToken);
                if ($returnToken === false) {
                    $semicolon = $phpcsFile->findNext(T_WHITESPACE, ($returnToken + 1), null, true);
                    if ($tokens[$semicolon]['code'] === T_SEMICOLON) {
                        $error = 'Missing @return tag in function comment';
                        $phpcsFile->addError($error, $tokens[$commentStart]['comment_closer'], 'MissingReturn');
                    }
                } else {
                    $error = 'Missing @return tag in function comment';
                    $phpcsFile->addError($error, $tokens[$commentStart]['comment_closer'], 'MissingReturn');
                }
            }
        }
    }

    /**
     * Returns a valid variable type for param/var tag.
     *
     * If type is not one of the standard type, it must be a custom type.
     * Returns the correct type name suggestion if type name is invalid.
     *
     * @param string $varType The variable type to process.
     *
     * @return string
     */
    private function suggestType($varType)
    {
        if ($varType === '') {
            return '';
        }

        if (in_array($varType, self::$allowedTypes) === true) {
            return $varType;
        } else {
            $lowerVarType = strtolower($varType);
            switch ($lowerVarType) {
                case 'boolean':
                    return 'bool';
                case 'double':
                case 'real':
                    return 'float';
                case 'integer':
                    return 'int';
                case 'array()':
                    return 'array';
            }

            if (strpos($lowerVarType, 'array(') !== false) {
                // Valid array declaration:
                // array, array(type), array(type1 => type2).
                $matches = [];
                $pattern = '/^array\(\s*([^\s^=^>]*)(\s*=>\s*(.*))?\s*\)/i';
                if (preg_match($pattern, $varType, $matches) !== 0) {
                    $type1 = '';
                    if (isset($matches[1]) === true) {
                        $type1 = $matches[1];
                    }

                    $type2 = '';
                    if (isset($matches[3]) === true) {
                        $type2 = $matches[3];
                    }

                    $type1 = $this->suggestType($type1);
                    $type2 = $this->suggestType($type2);
                    if ($type2 !== '') {
                        $type2 = ' => '.$type2;
                    }

                    return "array($type1$type2)";
                } else {
                    return 'array';
                }
            } else if (in_array($lowerVarType, self::$allowedTypes) === true) {
                // A valid type, but not lower cased.
                return $lowerVarType;
            } else {
                // Must be a custom type name.
                return $varType;
            }
        }

    }

    /**
     * Process the function parameter comments.
     *
     * @param PHP_CodeSniffer_File $phpcsFile    The file being scanned.
     * @param int                  $stackPtr     The position of the current token
     *                                           in the stack passed in $tokens.
     * @param int                  $commentStart The position in the stack where the comment started.
     *
     * @return void
     */
    protected function processParams(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $commentStart)
    {
        $tokens = $phpcsFile->getTokens();
        $params = [];
        $maxType = 0;
        $maxVar = 0;
        foreach ($tokens[$commentStart]['comment_tags'] as $pos => $tag) {
            if ($tokens[$tag]['content'] !== '@param') {
                continue;
            }
            $type = '';
            $typeSpace = 0;
            $var = '';
            $varSpace = 0;
            $comment = '';
            $commentLines = [];
            if ($tokens[($tag + 2)]['code'] === T_DOC_COMMENT_STRING) {
                $matches = [];
                preg_match('/([^$&]+)(?:((?:\$|&)[^\s]+)(?:(\s+)(.*))?)?/', $tokens[($tag + 2)]['content'], $matches);
                $typeLen = strlen($matches[1]);
                $type = trim($matches[1]);
                $typeSpace = ($typeLen - strlen($type));
                $typeLen = strlen($type);
                if ($typeLen > $maxType) {
                    $maxType = $typeLen;
                }
                if (isset($matches[2]) === true) {
                    $var = $matches[2];
                    $varLen = strlen($var);
                    if ($varLen > $maxVar) {
                        $maxVar = $varLen;
                    }
                    if (isset($matches[4]) === true) {
                        $varSpace = strlen($matches[3]);
                        $comment = $matches[4];
                        $commentLines[] = [
                            'comment' => $comment,
                            'token' => ($tag + 2),
                            'indent' => $varSpace,
                        ];
                        // Any strings until the next tag belong to this comment.
                        if (isset($tokens[$commentStart]['comment_tags'][($pos + 1)]) === true) {
                            $end = $tokens[$commentStart]['comment_tags'][($pos + 1)];
                        } else {
                            $end = $tokens[$commentStart]['comment_closer'];
                        }
                        for ($i = ($tag + 3); $i < $end; $i++) {
                            if ($tokens[$i]['code'] === T_DOC_COMMENT_STRING) {
                                $indent = 0;
                                if ($tokens[($i - 1)]['code'] === T_DOC_COMMENT_WHITESPACE) {
                                    $indent = strlen($tokens[($i - 1)]['content']);
                                }
                                $comment .= ' '.$tokens[$i]['content'];
                                $commentLines[] = [
                                    'comment' => $tokens[$i]['content'],
                                    'token' => $i,
                                    'indent' => $indent,
                                ];
                            }
                        }
                    }
                } else {
                    $error = 'Missing parameter name';
                    $phpcsFile->addError($error, $tag, 'MissingParamName');
                }
            } else {
                $error = 'Missing parameter type';
                $phpcsFile->addError($error, $tag, 'MissingParamType');
            }
            $params[] = [
                'tag' => $tag,
                'type' => $type,
                'var' => $var,
                'comment' => $comment,
                'commentLines' => $commentLines,
                'type_space' => $typeSpace,
                'var_space' => $varSpace,
            ];
        }
        $realParams = $phpcsFile->getMethodParameters($stackPtr);
        $foundParams = [];
        foreach ($params as $pos => $param) {
            // If the type is empty, the whole line is empty.
            if ($param['type'] === '') {
                continue;
            }
            // Check the param type value.
            $typeNames = explode('|', $param['type']);
            foreach ($typeNames as $typeName) {
                $suggestedName = $this->suggestType($typeName);
                if ($typeName !== $suggestedName) {
                    $error = 'Expected "%s" but found "%s" for parameter type';
                    $data = [
                        $suggestedName,
                        $typeName,
                    ];
                    $fix = $phpcsFile->addFixableError($error, $param['tag'], 'IncorrectParamVarName', $data);
                    if ($fix === true) {
                        $content = $suggestedName;
                        $content .= str_repeat(' ', $param['type_space']);
                        $content .= $param['var'];
                        $content .= str_repeat(' ', $param['var_space']);
                        if (isset($param['commentLines'][0]) === true) {
                            $content .= $param['commentLines'][0]['comment'];
                        }
                        $phpcsFile->fixer->replaceToken(($param['tag'] + 2), $content);
                    }
                } else if (count($typeNames) === 1) {
                    // Check type hint for array and custom type.
                    $suggestedTypeHint = '';
                    if (strpos($suggestedName, 'array') !== false) {
                        $suggestedTypeHint = 'array';
                    } else if (strpos($suggestedName, 'callable') !== false) {
                        $suggestedTypeHint = 'callable';
                    } else if (in_array($typeName, self::$allowedTypes) === false) {
                        $suggestedTypeHint = $suggestedName;
                    }
                    if ($suggestedTypeHint !== '' && isset($realParams[$pos]) === true) {
                        $typeHint = $realParams[$pos]['type_hint'];
                        if ($typeHint === '') {
                            $error = 'Type hint "%s" missing for %s';
                            $data = [
                                $suggestedTypeHint,
                                $param['var'],
                            ];
                            $phpcsFile->addError($error, $stackPtr, 'TypeHintMissing', $data);
                        } else if ($typeHint !== substr($suggestedTypeHint, (strlen($typeHint) * -1))) {
                            $error = 'Expected type hint "%s"; found "%s" for %s';
                            $data = [
                                $suggestedTypeHint,
                                $typeHint,
                                $param['var'],
                            ];
                            $phpcsFile->addError($error, $stackPtr, 'IncorrectTypeHint', $data);
                        }
                    } else if ($suggestedTypeHint === '' && isset($realParams[$pos]) === true) {
                        $typeHint = $realParams[$pos]['type_hint'];
                        if ($typeHint !== '' && in_array($typeHint, self::$allowedTypes) === false) {
                            $error = 'Unknown type hint "%s" found for %s';
                            $data = [
                                $typeHint,
                                $param['var'],
                            ];
                            $phpcsFile->addError($error, $stackPtr, 'InvalidTypeHint', $data);
                        }
                    }
                }
            }
            if ($param['var'] === '') {
                continue;
            }
            $foundParams[] = $param['var'];
            // Check number of spaces after the type.
            $spaces = ($maxType - strlen($param['type']) + 1);
            if ($param['type_space'] !== $spaces) {
                $error = 'Expected %s spaces after parameter type; %s found';
                $data = [
                    $spaces,
                    $param['type_space'],
                ];
                $fix = $phpcsFile->addFixableError($error, $param['tag'], 'SpacingAfterParamType', $data);
                if ($fix === true) {
                    $phpcsFile->fixer->beginChangeset();
                    $content = $param['type'];
                    $content .= str_repeat(' ', $spaces);
                    $content .= $param['var'];
                    $content .= str_repeat(' ', $param['var_space']);
                    $content .= $param['commentLines'][0]['comment'];
                    $phpcsFile->fixer->replaceToken(($param['tag'] + 2), $content);
                    // Fix up the indent of additional comment lines.
                    foreach ($param['commentLines'] as $lineNum => $line) {
                        if ($lineNum === 0
                            || $param['commentLines'][$lineNum]['indent'] === 0
                        ) {
                            continue;
                        }
                        $newIndent = ($param['commentLines'][$lineNum]['indent'] + $spaces - $param['type_space']);
                        $phpcsFile->fixer->replaceToken(
                            ($param['commentLines'][$lineNum]['token'] - 1),
                            str_repeat(' ', $newIndent)
                        );
                    }
                    $phpcsFile->fixer->endChangeset();
                }
            }
            // Make sure the param name is correct.
            if (isset($realParams[$pos]) === true) {
                $realName = $realParams[$pos]['name'];
                if ($realName !== $param['var']) {
                    $code = 'ParamNameNoMatch';
                    $data = [
                        $param['var'],
                        $realName,
                    ];
                    $error = 'Doc comment for parameter %s does not match ';
                    if (strtolower($param['var']) === strtolower($realName)) {
                        $error .= 'case of ';
                        $code = 'ParamNameNoCaseMatch';
                    }
                    $error .= 'actual variable name %s';
                    $phpcsFile->addError($error, $param['tag'], $code, $data);
                }
            } else if (substr($param['var'], -4) !== ',...') {
                // We must have an extra parameter comment.
                $error = 'Superfluous parameter comment';
                $phpcsFile->addError($error, $param['tag'], 'ExtraParamComment');
            }
            if ($param['comment'] === '') {
                continue;
            }
            // Check number of spaces after the var name.
            $spaces = ($maxVar - strlen($param['var']) + 1);
            if ($param['var_space'] !== $spaces) {
                $error = 'Expected %s spaces after parameter name; %s found';
                $data = [
                    $spaces,
                    $param['var_space'],
                ];
                $fix = $phpcsFile->addFixableError($error, $param['tag'], 'SpacingAfterParamName', $data);
                if ($fix === true) {
                    $phpcsFile->fixer->beginChangeset();
                    $content = $param['type'];
                    $content .= str_repeat(' ', $param['type_space']);
                    $content .= $param['var'];
                    $content .= str_repeat(' ', $spaces);
                    $content .= $param['commentLines'][0]['comment'];
                    $phpcsFile->fixer->replaceToken(($param['tag'] + 2), $content);
                    // Fix up the indent of additional comment lines.
                    foreach ($param['commentLines'] as $lineNum => $line) {
                        if ($lineNum === 0
                            || $param['commentLines'][$lineNum]['indent'] === 0
                        ) {
                            continue;
                        }
                        $newIndent = ($param['commentLines'][$lineNum]['indent'] + $spaces - $param['var_space']);
                        $phpcsFile->fixer->replaceToken(
                            ($param['commentLines'][$lineNum]['token'] - 1),
                            str_repeat(' ', $newIndent)
                        );
                    }
                    $phpcsFile->fixer->endChangeset();
                }
            }
            // Param comments must start with a capital letter and end with the full stop.
            $firstChar = $param['comment']{0};
            if (preg_match('|\p{Lu}|u', $firstChar) === 0) {
                $error = 'Parameter comment must start with a capital letter';
                $phpcsFile->addError($error, $param['tag'], 'ParamCommentNotCapital');
            }
            $lastChar = substr($param['comment'], -1);
            if ($lastChar !== '.') {
                $error = 'Parameter comment must end with a full stop';
                $phpcsFile->addError($error, $param['tag'], 'ParamCommentFullStop');
            }
        }
    }
}
