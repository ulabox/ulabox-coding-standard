[![Build Status](https://secure.travis-ci.org/escapestudios/Symfony2-coding-standard.png)](http://travis-ci.org/escapestudios/Symfony2-coding-standard)

# Symfony2 PHP CodeSniffer Coding Standard

A coding standard to check against the [Symfony coding standards](http://symfony.com/doc/current/contributing/code/standards.html), originally shamelessly copied from the -disappeared- opensky/Symfony2-coding-standard repository.

## Installation

### Composer

This standard can be installed with the [Composer](https://getcomposer.org/) dependency manager.

1. [Install Composer](https://getcomposer.org/doc/00-intro.md)

2. Install the coding standard as a dependency of your project

        composer require --dev escapestudios/symfony2-coding-standard:dev-master

3. Add the coding standard to the PHP_CodeSniffer install path

        bin/phpcs --config-set installed_paths vendor/escapestudios/symfony2-coding-standard

5. Check the installed coding standards for "Symfony2" and "Ulabox"

        bin/phpcs -i

5. Set the default coding standars to "Ulabox"

        bin/phpcs --config-set default_standard Ulabox

6. Done!

        vendor/bin/phpcs /path/to/code

