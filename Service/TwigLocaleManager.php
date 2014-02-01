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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;

class TwigLocaleManager
{
    protected $container;

    /** @var LocaleManager $localeManager */
    protected $localeManager;

    protected $localesConfig;

    public function __construct($container, LocaleManager $localeManager, array $localesConfig)
    {
        $this->container = $container;
        $this->localeManager = $localeManager;
        $this->localesConfig = $localesConfig;
    }

    /**
     * Get allowed language items by top level domain
     *
     * @return array
     */
    public function getItems()
    {
        $request = $this->container->get('request');
        $tld = $this->localeManager->getTopLevelDomain($request);
        $locales = $this->localeManager->getAllowedLocales($tld);

        $items = array();
        foreach ($locales['languages'] as $language) {
            $item = array();
            $config = $this->getLocaleConfigByKey($language);

            $item['key'] = $language;
            $item['image'] = $config['image'];
            $item['label'] = $config['label'];

            $items[] = $item;
        }

        return $items;
    }

    /**
     * Get configuration of given locale
     * key, Image and Label
     *
     * @param $key
     * @return array
     */
    protected function getLocaleConfigByKey($key)
    {
        $data = array(
            'image' => '',
            'label' => ''
        );

        if (!isset($this->localesConfig[$key])) {
            return $data;
        }

        $config = $this->localesConfig[$key];

        $data['image'] = (isset($config['image'])) ? $config['image'] : '';
        $data['label'] = (isset($config['label'])) ? $config['label'] : '';

        return $data;
    }



}