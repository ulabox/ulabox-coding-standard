<?php

/**
 * Validates that we don't things like Interface, Trait
 * Abstract, Entity, Repository, Service... on class names
 */

class Ulabox_Sniffs_NamingConventions_ValidClassNameSniff implements PHP_CodeSniffer_Sniff
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
            T_INTERFACE,
            T_TRAIT,
            T_EXTENDS,
            T_ABSTRACT
        );
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile All the tokens found in the document.
     * @param int                  $stackPtr  The position of the current token in
     *                                        the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens   = $phpcsFile->getTokens();
        $line     = $tokens[$stackPtr]['line'];

        while ($tokens[$stackPtr]['line'] == $line) {

            /*
             * Suffix interfaces with Interface;
             */
            if ('T_INTERFACE' == $tokens[$stackPtr]['type']) {
                $name = $phpcsFile->findNext(T_STRING, $stackPtr);

                if ($name && substr($tokens[$name]['content'], -9) == 'Interface') {
                    $phpcsFile->addError(
                        'Interface name is suffixed with "Interface"',
                        $stackPtr,
                        'Invalid'
                    );
                }
            }

            /*
             * Suffix traits with Trait;
             */
            if ('T_TRAIT' == $tokens[$stackPtr]['type']) {
                $name = $phpcsFile->findNext(T_STRING, $stackPtr);

                if ($name && substr($tokens[$name]['content'], -5) == 'Trait') {
                    $phpcsFile->addError(
                        'Trait name is suffixed with "Trait"',
                        $stackPtr,
                        'Invalid'
                    );
                }
            }

            /*
             * Prefix abstract classes with Abstract.
             */
            if ('T_ABSTRACT' == $tokens[$stackPtr]['type']) {
                $name = $phpcsFile->findNext(T_STRING, $stackPtr);
                $function = $phpcsFile->findNext(T_FUNCTION, $stackPtr);

                // making sure we're not dealing with an abstract function
                if ($name && (is_null($function) || $name < $function) && substr($tokens[$name]['content'], 0, 8) == 'Abstract') {
                    $phpcsFile->addError(
                        'Abstract class name is prefixed with "Abstract"',
                        $stackPtr,
                        'Invalid'
                    );
                }
            }

            /*
             * Suffix classes with Entity;
             */
            if ('T_CLASS' == $tokens[$stackPtr]['type']) {
                $name = $phpcsFile->findNext(T_STRING, $stackPtr);

                if ($name && substr($tokens[$name]['content'], -6) == 'Entity') {
                    $phpcsFile->addError(
                        sprintf('Class name "%s" is suffixed with "Entity"', $tokens[$name]['content']),
                        $stackPtr,
                        'Invalid'
                    );
                }

                /*
                 * Suffix classes with Service;
                 */
                $name = $phpcsFile->findNext(T_STRING, $stackPtr);

                if ($name && substr($tokens[$name]['content'], -7) == 'Service') {
                    $phpcsFile->addError(
                        sprintf('Class name "%s" is suffixed with "Service"', $tokens[$name]['content']),
                        $stackPtr,
                        'Invalid'
                    );
                }

                /*
                 * Suffix classes with Repository;
                 */
                $name = $phpcsFile->findNext(T_STRING, $stackPtr);

                if ($name && substr($tokens[$name]['content'], -10) == 'Repository') {
                    $phpcsFile->addError(
                        sprintf('Class name "%s" is suffixed with "Repository"', $tokens[$name]['content']),
                        $stackPtr,
                        'Invalid'
                    );
                }

                /*
                 * Suffix classes with Command;
                 */
                $name = $phpcsFile->findNext(T_STRING, $stackPtr);

                if ($name && substr($tokens[$name]['content'], -7) == 'Command') {
                    $phpcsFile->addError(
                        sprintf('Class name "%s" is suffixed with "Command"', $tokens[$name]['content']),
                        $stackPtr,
                        'Invalid'
                    );
                }

                /*
                 * Suffix classes with Handler;
                 */
                $name = $phpcsFile->findNext(T_STRING, $stackPtr);

                if ($name && substr($tokens[$name]['content'], -7) == 'Handler') {
                    $phpcsFile->addError(
                        sprintf('Class name "%s" is suffixed with "Handler"', $tokens[$name]['content']),
                        $stackPtr,
                        'Invalid'
                    );
                }
            }
            $stackPtr++;
        }

        return;
    }
}
