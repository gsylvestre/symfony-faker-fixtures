# Faker Fixtures Maker for Symfony

This bundle allows fast generation of realistic fake datas for your database, in the `make` style that we love.  
Huge thanks to the wonderfull [Faker library](https://github.com/fzaninotto/Faker/).

### Main features

- Faker method selection based on entities attributes
- Handling of ManyToMany, ManyToOne, OneToMany and OneToOne association
- Fixtures order based on association dependencies
- Generation of standalone commands for each of your entities

Main requirements
============
If you are using the _symfony/website-skeleton_, you are good to go.  
Else:
- [Symfony Framework](https://github.com/symfony/symfony) >= 3.4
- [Doctrine ORM](https://github.com/doctrine/orm) >= 2.3
- [Symfony Maker Bundle](https://github.com/symfony/maker-bundle)

Installation
============

Open a command console, enter your project directory and execute:

```console
$ composer require gsylvestre/symfony-faker-fixtures
```

That's it, your bundle is ready to use! See "How to use" section below.

How to use
============
1. Execute the following command:
   ```console
   $ php bin/console make:faker-fixtures
   ```
   This will generate your new fixtures commands in `src\Command\FakerFixtures\`.

2. Run the meta fixture command with:
   ```console
   $ php bin/console app:fixtures:load-all
   ```
   This will load magic datas in your database.

### Editing the fixtures
This bundle generates fixtures for you. Once done, you are free to edit them.

If needed (and it should), edit the generated commands: 

1. `LoadAllFixturesCommand.php` to adapt the number of entities to generate or the order
2. All other fixtures files to adapt the Faker methods used and/or the logic

### Start over?
If you ever need to regenerates all your fixtures, you can do so by running: 
```console
$ php bin/console make:faker-fixtures --delete-previous
```
Be aware that *you will lose all changes* made to your fixtures commands!

### Running only one fixture?
Each entity fixture is a seperate command that you can run with:
```console
$ php bin/console app:fixtures:entityName 50
```
Replace *entityName* with your entity name, and 50 with the number of rows to create.

You can always find the command to run in each fixture file.

Installation without Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require gsylvestre/symfony-faker-fixtures
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...
            new FakerFixtures\FakerFixturesBundle(),
        ];

        // ...
    }

    // ...
}
```

## Notes
This bundle deliberately does not use DoctrineFixturesBundle in any way.