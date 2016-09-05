#SilverStripe Field Comment Module
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ishannz/silverstripe-field-comment/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ishannz/silverstripe-field-comment/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/ishannz/silverstripe-field-comment/badges/build.png?b=master)](https://scrutinizer-ci.com/g/ishannz/silverstripe-field-comment/build-status/master)

#Summary
This module lets you attach some comments to individual fields in the backend of the CMS.

## Installation

The Field-comment SilverStripe module can be installed via [Composer](http://getcomposer.org) by requiring the
`ishannz/silverstripe-field-comment` package and setting the `minimum-stability` to `dev` (required for SilverStripe 3.1) in your
project's `composer.json`.

```json
{
    "require": {
        "ishannz/silverstripe-field-comment": "dev-master"
    },
    "minimum-stability": "dev"
}
```

or

Require this package with composer:
```
composer require ishannz/silverstripe-field-comment
```
Update your `composer.json` file to include this package as a dependency

Update your packages with ```composer update``` or install with ```composer install```.

In Windows, you'll need to include the GD2 DLL `php_gd2.dll` as an extension in php.ini.

#Configuration
To get comments on fields, you need to do two things:
- You need to put the field names in a static variable called commentable_fields, on the DataObject

ie
```
  class MyPage extends Page {

	static $db = array(
		'MyField' => 'VarChar(8)'
    );
    static $commentable_fields = array(
	    'MyField'
    );

    function getCMSFields(){
	    $fields = parent::getCMSFields();
        $fields->addFieldToTab('Root.Content.Main', new TextField('MyField', 'My field'));
		return $fields;
    }
  }
  ```
