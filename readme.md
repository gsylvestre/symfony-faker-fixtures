# Faker Fixtures Maker for Symfony

This bundle allows fast generation of realistic fake datas for your database, in the `make` style that we love. Huge thanks to the wonderfull [Faker library](https://github.com/fzaninotto/Faker/).

### Main features

- Quick and easy to use
- Faker method selection based on entities attributes
- Handling of ManyToMany, ManyToOne, OneToMany and OneToOne associations
- Fixtures order based on association dependencies


Installation
============

Open a command console, enter your project directory and execute:

```console
$ composer require gsylvestre/symfony-faker-fixtures
```

---

How to use
============
1. Execute the following command:
   ```console
   $ php bin/console make:faker-fixtures
   ```
   This will generate your new fixture command in `src\Command\`.

2. Run the fixture command with:
   ```console
   $ php bin/console app:fixtures:load
   ```
   This will load magic datas in your database.


### Editing the fixtures
This bundle generates fixtures for you. Once done, you are free to edit them.

If needed (and it should), edit the generated `FakerFixturesCommand.php`: 

1. To adapt the number of entities to generate, or the order
2. To adapt the Faker methods used and/or the logic

### Start over?
If you ever need to regenerates all your fixtures, you can do so by running: 
```console
$ php bin/console make:faker-fixtures --delete-previous
```
Be aware that *you will lose all changes* made to your fixtures command!

### Localize faker datas?
If you want your generated datas localized, run: 
```console
$ php bin/console make:faker-fixtures --locale=fr_FR
```


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