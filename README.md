# Ulabox Coding Standard

A coding standard to check against the Ulabox coding standards, originally copied from the escapestudios/Symfony2-coding-standard repository.

The coding standard is different if its used on Behat or PhpSpec. Remember to add the correct path in each case when calling `phpcs --standard="path/to/ruleset" file`. 

### build.xml example
    <target name="phpcs" description="Runs Ulabox PHPCS">
        <exec dir="${basedir}" executable="bin/phpcs" failonerror="true">
            <arg value="--report-full" />
            <arg value="--report-checkstyle=build/phpcs.xml" />
            <arg value="--standard=vendor/ulabox/ulabox-coding-standard/Ulabox/ruleset.xml" />
            <arg value="--ignore=cache,logs,js,Doctrine,app" />
            <arg value="${sourcedir}" />
        </exec>
        <exec dir="${basedir}" executable="bin/phpcs" failonerror="true">
            <arg value="--report-full" />
            <arg value="--report-checkstyle=build/phpcs.xml" />
            <arg value="--standard=vendor/ulabox/ulabox-coding-standard/Ulabox-Behat/ruleset.xml" />
            <arg value="${basedir}/tests/Functional/Context" />
        </exec>

        <exec dir="${basedir}" executable="bin/phpcs" failonerror="true">
            <arg value="--report-full" />
            <arg value="--report-checkstyle=build/phpcs.xml" />
            <arg value="--standard=vendor/ulabox/ulabox-coding-standard/Ulabox-PhpSpec/ruleset.xml" />
            <arg value="${basedir}/tests/Unit" />
        </exec>
    </target>

##  Ulabox Rules

Ulabox follows the [PRS-2](https://github.com/squizlabs/PHP_CodeSniffer/tree/master/CodeSniffer/Standards/PSR2), [Zend](https://github.com/squizlabs/PHP_CodeSniffer/tree/master/CodeSniffer/Standards/Zend) and [PEAR](https://github.com/squizlabs/PHP_CodeSniffer/tree/master/CodeSniffer/Standards/PEAR) standard plus the following rules:

1. Arrays

  - Ensure that there are no spaces around square brackets.

2. Commenting:

  - Function comments must have param, throws and return tags if necessary.

  - Variables comments must have var tag with var type.

  - Ensure that no perl-style comments are used.

3. Control Structures:

  - Ensure that control statements conform to their coding standards.
        
  - Ensure that there is a space between each condition of foreach loops.
        
  - Ensure that there is a space between each condition of for loops.
        
  - Ensure all control structure keywords are lowercase.

  - Ensure that inline control statements are not present.

5. Formatting:

  - Ensure there is a single space after cast tokens.

6. Functions:

  - No setters and getters are allowed.
        
  - Ensure that variables are not passed by reference when calling a function.

7. Naming conventions:

  - The use of Interface, Trait, Abstract, ... on class names are not allowed.
        
  - Ensure that constant names are all uppercase.

8. PHP:

  - Lower case constant rule from the Generic standard.

  - Ensure all calls to inbuilt PHP functions are lowercase.

9. Scope:

  - Ensure that class members have scope modifiers.

10. Strings:

  - Ensure there are no spaces between the concatenation operator (.) and the strings being concatenated.

## Excluded Rules in Ulabox

The following rules are excluded from the Zend and PEAR standards:

2. Control Structures:

  - Ensure that control statements conform to their coding standards.

3. Debug:

  - Code analyzer from the Zend standard.

4. Functions:

  - Opening parenthesis of a multi-line function call must be the last content on the line.
  - Closing parenthesis of a multi-line function call must be on a line by itself.

5. Naming Conventions:

  - Member variables must contain leading underscore.
  - Member variables shouldn't contain numbers.

## Excluded Rules in Ulabox-Behat

The Ulabox-Behat standard inherits from the Ulabox standard. The rules excluded are:

1. Comments:

  - The whole set of comment rules from Ulabox standard.
  - The 120 characters limit has been removed to allow long annotations in Behat Context files

2. Classes:

  - The whole set of classes rules from PRS1 standard.

## Excluded Rules in Ulabox-PhpSpec

The Ulabox-PhpSpec standard inherits from the Ulabox standard. The rules excluded are:

1. Comments:

  - The whole set of comment rules from Ulabox standard.
  - The whole set of comment rules from PEAR standard.

2. Scope:
  
  - The whole set of method scope rules from the Squiz standard.

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

