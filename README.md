# Ulabox Coding Standard

A coding standard to check against the Ulabox coding standards, originally copied from the escapestudios/Symfony2-coding-standard repository.

## Rules

Ulabox follows the [PRS-2](https://github.com/squizlabs/PHP_CodeSniffer/tree/master/CodeSniffer/Standards/PSR2), [Zend](https://github.com/squizlabs/PHP_CodeSniffer/tree/master/CodeSniffer/Standards/Zend) and [PEAR](https://github.com/squizlabs/PHP_CodeSniffer/tree/master/CodeSniffer/Standards/PEAR) standard plus the following rules:

1. Arrays

        - Array bracket spacing fule from the Squiz standard.

2. Commenting:

        - Function comments must have param, throws and return tags if necessary.

        - Variables comments must have var tag with var type.

        - Inline comment rule from the PEAR standard.

3. Control Structures:

        - Control signature rule from the Squiz standard.
        
        - For each loop declaration rule from the Squiz standard.
        
        - For lookp declaration rule from the Squiz standard.
        
        - Lowercase declaration rule from the Squiz standard.
        
4. Control Structure:

        - Inline control structure rule from the sGeneric standard.

5. Formatting:

        - Space after cast rule from the Generic standard.

6. Functions:

        - No setters and getters are allowed.
        
        - Call time pass by reference rule from the Generic standard.

7. Naming conventions:

        - The use of Interface, Trait, Abstract, ... on class names are not allowed.
        
        - Upper case constant name rule from the Generic standard.

8. PHP:

        - Lower case constant rule from the Generic standard.

        - Lower case PHP functions rule from the Squiz standard.

9. Scope:

        - Member var scope from the Squiz standard.

10. Strings:

        - Concatenation spacing rule from the Squiz standard.

## Excluded Rules

The following rules are excluded from the Zend and PEAR standards:

1. Commenting:

        - Spacing before tags rule from the PEAR standard.

2. Control Structures:

        - Control signature rule from the PEAR standard.

3. Debug:

        - Code analyzer rule from the Zend standard.

4. Functions:

        - Content after open bracket rule from the PEAR standard.
        - Close bracket line rule from the PEAR standard.

5. Naming Conventions:

        - Private no underscore rule from the Zend standard.
        - Contains numbers rule from the Zend standard.

## Installation

### Composer

This standard can be installed with the [Composer](https://getcomposer.org/) dependency manager.

1. [Install Composer](https://getcomposer.org/doc/00-intro.md)

2. Install the coding standard as a dependency of your project

        composer require --dev ulabox/ulabox-coding-standard:dev-master

3. Add the coding standard to the PHP_CodeSniffer install path

        bin/phpcs --config-set installed_paths vendor/ulabox/ulabox-coding-standard

5. Check the installed coding standards for "Ulabox"

        bin/phpcs -i

5. Set the default coding standars to "Ulabox"

        bin/phpcs --config-set default_standard Ulabox

6. Done!
        vendor/bin/phpcs /path/to/code

