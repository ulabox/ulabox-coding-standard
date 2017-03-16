<?php

/**
 * Validates that we don't use public setters and getters (e.g. setCode, getName) in our classes
 */
class Ulabox_Sniffs_Functions_ValidFunctionNameSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = [
        'PHP',
    ];

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [
            T_FUNCTION,
        ];
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $whitelistedNames = [
            '__construct',
            'setUp',
            'tearDown',
            'getMatchers',
            'getIterator',
        ];

        $tokens = $phpcsFile->getTokens();
        $methodProperties = $phpcsFile->getMethodProperties($stackPtr);
        if ($methodProperties['scope'] == 'public') {
            $namePtr = $phpcsFile->findNext(T_STRING, $stackPtr + 1, $tokens[$stackPtr]['parenthesis_opener']);
            $name = $tokens[$namePtr]['content'];
            if (in_array($name, $whitelistedNames)) {
                return;
            }

            if ($this->isNeededByParentClass($name, $tokens)) {
                return;
            }

            if (substr($name, 0, 3) == 'set' && ctype_upper(substr($name, 3, 1))) {
                $phpcsFile->addWarning(
                    sprintf('Public setter "%s" starts with "set". Write a name that express a domain behavior', $name),
                    $stackPtr,
                    'Invalid'
                );
            }

            if (substr($name, 0, 3) == 'get' && ctype_upper(substr($name, 3, 1))) {
                $phpcsFile->addWarning(
                    sprintf('Public getter "%s" starts with "get". Write a name that express a domain behavior', $name),
                    $stackPtr,
                    'Invalid'
                );
            }
        }
    }

    private function isNeededByParentClass($methodName, array $tokens)
    {
        $classFQN = $this->resolveClassFQN($tokens);
        $reflection = new ReflectionClass($classFQN);

        return $this->isMethodDefinedInParent($methodName, $reflection) ||
            $this->isMethodDefinedInInterface($methodName, $reflection);
    }

    private function isMethodDefinedInInterface($methodName, $reflection)
    {
        $interfaces = $reflection->getInterfaces();
        if (empty($interfaces)) {
            return false;
        }
        foreach ($interfaces as $interface) {
            if ($interface->hasMethod($methodName)) {
                return true;
            }
        }

        return false;
    }

    private function isMethodDefinedInParent($methodName, $reflection)
    {
        $parent = $reflection->getParentClass();
        if (!$parent) {
            return $reflection->hasMethod($methodName);
        } else {
            return $this->isMethodDefinedInParent($methodName, $parent);
        }
    }

    private function resolveClassFQN(array $tokens)
    {
        $namespace = '';
        $class = '';
        $numTokens = count($tokens);
        for ($i = 0; $i < $numTokens; $i++) {
            if ($tokens[$i]['type'] === 'T_NAMESPACE') {
                for ($j = $i + 1; $j < $numTokens; $j++) {
                    if ($tokens[$j]['type'] === 'T_STRING') {
                        $namespace .= '\\'.$tokens[$j]['content'];
                    } else if ($tokens[$j]['content'] === '{' || $tokens[$j]['content'] === ';') {
                        break;
                    }
                }
            }
            if ($tokens[$i]['type'] === 'T_CLASS' || $tokens[$i]['type'] === 'T_INTERFACE') {
                for ($j = $i + 1; $j < $numTokens; $j++) {
                    if ($tokens[$j]['content'] === '{') {
                        $class = $tokens[$i+2]['content'];
                    }
                }
            }
        }

        return $namespace.'\\'.$class;
    }
}
