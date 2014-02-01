MultiDomainLocaleBundle [![Build Status](https://travis-ci.org/thomaswiener/MultiDomainLocaleBundle.png?branch=master)](https://travis-ci.org/thomaswiener/MultiDomainLocaleBundle)
===================

## General

Why another locale bundle? Good question.

We used different approaches (jms i18n bundle, lunatics ...) to finally got our locale configuration up and running, but at the end we always had issues
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
        "twi/multi-domain-locale-bundle": "1.0.*@dev"
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
    new TWI\MultiDomainLocaleBundle\TWIMultiDomainLocaleBundle(),
    // ...
);
```

Installation is done. But you will need to configure a few thing before it really works.

## Configuration

Up front the whole configuration, explantion inline:

```
    # all allowed locales, new locales MUST be defined here!!!
    locales_allowed: de_DE|en_GB|de_CH|fr_FR     # mandatory, list all supported locales for your site

    # specify which locale is allowed in which country by top level domain
    localesByCountry:                            # mandatory
        ch:                                      # top level domain
            name: Switzerland                    # Name of Country
            locales: [de_CH, fr_FR, en_GB]       # Allowed locales for this domain
            default_locale: de_CH                # Fallback locale if none or unknown locale is given
        de:
            name: Germany
            locales: [de_DE, en_GB]
            default_locale: de_DE

        #add more countries here

        # default, is only for dev environments without a valid top level domain
        default:                                  # mandatory
            name: Germany
            locales: [de_DE, en_GB]
            default_locale: de_DE

    # Config data for locales, important for language dropdown menu
    locales_config:                               # mandatory
        de_DE:
            image: de.gif
            label: Deutsch
        de_CH:
            image: de_ch.gif
            label: Deutsch (Schweiz)
        fr_FR:
            image: fr.gif
            label: Français
        en_GB:
            image: en.gif
            label: English

    # Whitelist of routes, where locale validation is activated
    include_paths:                                  # mandatory
        - /demo

    # Regular Expression to find a locale pattern like 'de_DE' within a string (url)
    regex_locale: |\/(?P<locale>[a-z]{2}_[A-Z]{2})\/|   # optional

    # Cookie name for user locale
    cookie_name:  tl                                    # optional
```

In routing, add /{_locale} in front of all necessary routes.

```
login_check:
    pattern:   /{_locale}/login_check
    requirements:
        _locale: %locales_allowed%
```
