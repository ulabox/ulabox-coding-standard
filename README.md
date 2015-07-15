# Ulabox Coding Standard

A coding standard to check against the Ulabox coding standards, originally copied from the escapestudios/Symfony2-coding-standard repository.

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

