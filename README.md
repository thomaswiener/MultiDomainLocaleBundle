TWILocaleBundle
===================

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

```
php composer.phar update
```

```
<?php

// in AppKernel::registerBundles()
$bundles = array(
    // ...
    new TWI\LocaleBundle\TWILocaleBundle(),
    // ...
);
```


```
login_check:
    pattern:   /{_locale}/login_check
    requirements:
        _locale: %locales_allowed%
```
