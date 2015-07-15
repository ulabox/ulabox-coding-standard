<?php

/**
 * Validates that we don't use setters and getters (e.g. setCode, getName) in our classes
 */

class Ulabox_Sniffs_Functions_ValidFunctionNameSniff implements PHP_CodeSniffer_Sniff
{

    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = array(
        'PHP',
    );

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(
            T_CLASS,
            T_INTERFACE,
        );
    }//end register()

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
        $tokens = $phpcsFile->getTokens();
        $function = $stackPtr;

        $scopes = array(
            0 => T_PUBLIC,
            1 => T_PROTECTED,
            2 => T_PRIVATE,
        );

        $whitelisted = array(
            '__construct',
            'setUp',
            'tearDown',
        );

        while ($function) {
            $function = $phpcsFile->findNext(T_FUNCTION, $function + 1, $tokens[$stackPtr]['scope_closer']);

            if (isset($tokens[$function]['parenthesis_opener'])) {
                $scope = $phpcsFile->findPrevious(T_PUBLIC, $function -1, $stackPtr);
                $name = $phpcsFile->findNext(T_STRING, $function + 1, $tokens[$function]['parenthesis_opener']);

                if ($scope && $name && !in_array($tokens[$name]['content'], $whitelisted)) {

                    if (substr($tokens[$name]['content'], 0, 3) == 'set' && ctype_upper(substr($tokens[$name]['content'], 3, 1))) {
                        $phpcsFile->addError(
                            sprintf('Setter "%s" starts with "get". Write a name that express a domain behavior', $tokens[$name]['content']),
                            $stackPtr,
                            'Invalid'
                        );
                    }

                    if (substr($tokens[$name]['content'], 0, 3) == 'get' && ctype_upper(substr($tokens[$name]['content'], 3, 1))) {
                        $phpcsFile->addError(
                            sprintf('Getter "%s" starts with "get". Write a name that express a domain behavior', $tokens[$name]['content']),
                            $stackPtr,
                            'Invalid'
                        );
                    }
                }
            }
        }
    }
}
