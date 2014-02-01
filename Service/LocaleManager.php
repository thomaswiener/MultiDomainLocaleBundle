<?php

/*
 * Copyright 2014 Thomas Wiener <wiener.thomas@googlemail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace TWI\MultiDomainLocaleBundle\Service;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;

class LocaleManager
{
    /**
     * Permanente HTTP redirect code
     */
    const HTTP_REDIRECT_PERM = 301;

    /**
     * Temporary HTTP redirect code
     */
    const HTTP_REDIRECT_TEMP = 307;

    /**
     * Cookie Label for user locale
     */
    protected $cookieLocaleName;

    /**
     * Regular expression to find locale eg. de_DE in request url
     */
    protected  $regExLocale;

    /**
     * array @var Country specific settings
     */
    protected $localesByCountry;

    /**
     * array @var Array of path info fragments to be included in locale validations
     */
    protected $includePaths;

    /**
     * Setting arrays for locale validation purposes
     *
     * @param array $localesByCountry Locale specific info by domain
     * @param array $includePaths Path info fragments to be validatated for locale
     * @param $regExLocale Regular Expression to find locale in uri
     * @param $cookieLocaleName
     */
    public function __construct(
        array $localesByCountry,
        array $includePaths,
        $regExLocale,
        $cookieLocaleName)
    {
        $this->localesByCountry = $localesByCountry;
        $this->includePaths = $includePaths;
        $this->regExLocale = $regExLocale;
        $this->cookieLocaleName = $cookieLocaleName;
    }

    /**
     * Find locale in Path Info via regex
     *
     * @param $pathInfo
     * @return mixed
     */
    public function getLocalePathInfo($pathInfo)
    {
        preg_match($this->regExLocale, $pathInfo, $result); #PREG_OFFSET_CAPTURE

        return $result;
    }

    /**
     * Get locale, either via
     *     User settings, only if user is logged in,
     *     Locale Browsersettings or
     *     Default Domain Locale, set in parameters
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param array $allowedLocales
     * @return string
     */
    public function getLocale(Request $request, array $allowedLocales)
    {
        //first try to use user language from cookie, if user was logged in this value should be set
        $userDefault = $this->getLocaleFromCookie($request);

        if ($userDefault !== false) {
            return $userDefault;
        }

        //next try to use browser default
        $browserDefault = $this->getBrowserDefault($request, $allowedLocales['locales']);

        if ($browserDefault !== false) {
            return $browserDefault;
        }

        //finally use default language of domain
        return $allowedLocales['default_locale'];
    }

    /**
     * Returns locale set in cookie variable
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return bool return cookie string or false if not exists
     */
    public function getLocaleFromCookie(Request $request)
    {
        $cookies = $request->cookies;

        if (!$cookies->has($this->cookieLocaleName)) {
            return false;
        }

        $locale = $cookies->get($this->cookieLocaleName);
        $result = $this->getLocalePathInfo('/'.$locale.'/');

        if (!isset($result['locale'])) {
            return false;
        }

        return $result['locale'];
    }

    /**
     * Check if current locale is allowed as locale in url
     *
     * @param $locale Current locale
     * @param array $allowedLocales Allowed locales for this tld
     * @return bool
     */
    public function isLocaleAllowed($locale, $allowedLocales)
    {
        if (in_array($locale, $allowedLocales)) {
            return true;
        }

        return false;
    }

    /**
     * Get Browser default locale
     *
     * @param $request
     * @param $allowedLocales
     * @return string
     */
    public function getBrowserDefault(Request $request, $allowedLocales)
    {
        // use accept header for locale matching if sent
        if ($languages = $request->getLanguages()) {
            foreach ($languages as $lang) {
                if (in_array($lang, $allowedLocales, true)) {
                    return $lang;
                }
            }
        }

        return false;
    }

    /**
     * Get Top level domain from request host
     *
     * @param Request $request
     * @return mixed
     */
    public function getTopLevelDomain(Request $request)
    {
        $httpHost = $request->getHost();
        $parts = explode('.', $httpHost);

        return end($parts);
    }

    /**
     * Remove an invalid locale from pathInfo and return normalized pathInfo
     *
     * @param $pathInfo
     * @param $locale
     * @return string
     */
    public function removeInvalidLocale($pathInfo, $locale)
    {
        $parts = explode("/", $pathInfo);

        foreach ($parts as $key => $part) {
            if ($part == $locale) {
                unset($parts[$key]);
            }
        }

        return implode("/", $parts);
    }

    /**
     * Get allowed locales by top level domain,
     * if it does not exist, return default config
     *
     * @param $tld top level domain
     * @return array
     */
    public function getAllowedLocales($tld)
    {
        $tld = strtolower($tld);
        $locales = (isset($this->localesByCountry[$tld])) ?
            $this->localesByCountry[$tld] : array();

        if ($locales == array()) {
            return $this->localesByCountry['default'];
        }

        return $locales;
    }

    /**
     * Checks if pathinfo is in include path array
     * if true, do a locale validation
     * else continue without and return
     *
     * @param $pathInfo Path info of uri
     * @return bool true if include, else false
     */
    public function isPathIncluded($pathInfo)
    {
        //remove locale if exists from path info
        $result = $this->getLocalePathInfo($pathInfo);
        if (isset($result['locale'])) {
            $locale = $result['locale'];
            $pathInfo = $this->removeInvalidLocale($pathInfo, $locale);
        }

        // '/' is a special case, so do it manually
        if ($pathInfo == '/') {
            return true;
        }

        foreach ($this->includePaths as $path) {
            if (0 === strpos($pathInfo, $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Redirect with new locale settings
     *
     * @param $baseUrl
     * @param $pathInfo
     * @param $locale
     * @param array $params
     * @return RedirectResponse
     */
    public function redirect($baseUrl, $pathInfo, $locale, array $params)
    {
        return new RedirectResponse(
            $baseUrl . '/' . $locale . $pathInfo . ($params ? '?'.http_build_query($params) : ''),
            LocaleManager::HTTP_REDIRECT_TEMP
        );
    }

    public function determineUrl(Request $request, $url, $language)
    {
        //search for locale in redirect url
        $result = $this->getLocalePathInfo($url);
        //if found replace locale with user locale
        if (isset($result['locale'])) {
            $request->setLocale($result['locale']);
            return str_replace($result['locale'], $language, $url);
        }

        return $url;
    }

}