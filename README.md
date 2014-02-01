MultiDomainLocaleBundle [![Build Status](https://travis-ci.org/thomaswiener/TWILocaleBundle.png?branch=master)](https://travis-ci.org/thomaswiener/TWILocaleBundle)
===================

## General

Why another locale bundle? Good question.

We used different approaches (jms i18n bundle, lunatics ...) to finally get our locale configuration up and running, but at the end we always had issues
with requirements that were not included.

This bundle is for you if....

* you are using locales that combine language and country: de_DE, fr_FR, de_CH
* you have different domains (mycompany.de, mycompany.ch, mycompany.fr)
* you want to allow only certain locales for certain domains (eg. .de => de_DE ; .ch => de_CH, fr_CH, it_CH ; .fr => fr_FR)

## Installation

The installation of MultiDomainLocaleBundle is pretty straight forward. Just add the following to your composer.json:

```
// composer.json
{
    // ...
    require: {
        // ...
        "twi/locale-bundle": "1.0.*@dev"
    }
}
```

Update your lib with composer update:

```
php composer.phar update
```

Add the bundle to your AppKernel:

```
<?php

// in AppKernel::registerBundles()
$bundles = array(
    // ...
    new TWI\LocaleBundle\TWILocaleBundle(),
    // ...
);
```

Installation is done. But you will need to configure a few thing before it really works.

## Configuration

Define available locales
Define Domains and allowed countries
Whitelist routes

```
login_check:
    pattern:   /{_locale}/login_check
    requirements:
        _locale: %locales_allowed%
```
