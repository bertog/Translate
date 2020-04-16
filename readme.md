# Translate

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Build Status][ico-travis]][link-travis]
[![StyleCI][ico-styleci]][link-styleci]

A very simple package to translate everything you need in a Laravel Model and present to the user the field in the language set in the application

## Installation

Via Composer

``` bash
$ composer require thenonsensefactory/translate
```

## Usage

First publish the needed migration:

``` bash
$ php artisan vendor:publish --provider='TheNonsenseFactory\Translate\TranslateServiceProvider' --tags="migrations"
```
And run the migration. Now your database have a new table called 'translations'

In order to have a model Transatable add the relative Trait

``` php
<?php

    use TheNonsenseFactory\Translate\Traits\Translatable;

    class Article extends Model {
        
        use Translatable;

    }
```

Then you have to declare what you need to translate in the $translatable array

``` php
<?php

    use TheNonsenseFactory\Translate\Traits\Translatable;

    class Article extends Model {
        
        use Translatable;

        protected $translatable = ['title', 'body'];

    }
```

To Save a new translation you can do in this way:

``` php

article->translations()->create([
        'lang' => 'en',
        'field' => 'title',
        'text' => 'My Fancy Title'
    ]);

```

The magic came when you access to the field you have declared as Translatable. 
The package return the translation in the current App language if present or fallback in the Model table data.

``` php

//If the App Locale is 'en'

$article->title //Provide the en translation (if present)

//If the App locale is 'it'

$article->title //Provide the it translation (if present)

//If the App locale is 'de' and the translation does not exsist

$article->title //Provide the title from the Articles Table
```
You have a Query Scope and a useful help method

``` php

$article->translations()->currentLang() //Provide all the translations in the current App Locale set

$article->updateOrCreateTranslation($array) // Update a translation if present or create a new one in the current Language set in App Locale

```
The Array that you have to give to the updateOrCreateTranslation method must be in this shape:

    field => text

for example:
    
    ['title' => 'My Fancy Title Updated']

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.


## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email author email instead of using the issue tracker.

## Credits

- [The Nonsense Factory][link-author]
- [All Contributors][link-contributors]

## License

license. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/thenonsensefactory/translate.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/thenonsensefactory/translate.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/thenonsensefactory/translate/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/thenonsensefactory/translate
[link-downloads]: https://packagist.org/packages/thenonsensefactory/translate
[link-travis]: https://travis-ci.org/thenonsensefactory/translate
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://www.thenonsensefactory.it
[link-contributors]: ../../contributors
