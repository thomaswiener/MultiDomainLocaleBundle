<?php
namespace TWI\LocaleBundle\EventListener;

use parku\LocaleBundle\Service\LocaleManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Locale Validation Class for routes
 * Checks if a locale exists, or a given locale is valid for given top level domain (allowed locales by country/domain are
 * defined in security.yml of this bundle)
 *
 * Class LocaleChoosingListener
 * @package parku\LocaleBundle\EventListener
 */
class LocaleListener
{
    protected $localeManager;

    public function __construct(LocaleManager $localeManager)
    {
        $this->localeManager = $localeManager;
    }

    /**
     * Intercept all requests and validate locale, replace if necessary and redirect
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        //only listen to master requests
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        /** @var Request $request */
        $request = $event->getRequest();
        $params = $request->query->all();

        //retrieve path info and top level domain
        $tld = $this->localeManager->getTopLevelDomain($request);
        $pathInfo = $request->getPathInfo();

        //check if path info is in whitelist, if not, return without locale validation
        //otherwise return and continue
        if (!$this->localeManager->isPathIncluded($pathInfo)) {
            return;
        }

        $allowedLocales = $this->localeManager->getAllowedLocales($tld);
        $result = $this->localeManager->getLocalePathInfo($pathInfo);

        //if locale was found
        if (isset($result['locale'])) {

            $locale = $result['locale'];
            //check if locale is allowed for list of domain locales
            if ($this->localeManager->isLocaleAllowed($locale, $allowedLocales['languages'])) {
                //locale is allowed for current request, continue
                return;
            }
            //given locale is unknown, remove current locale in pathinfo
            $pathInfo = $this->localeManager->removeInvalidLocale($pathInfo, $locale);

            //permanently redirect to new path without locale!!
            $event->setResponse(
                new RedirectResponse(
                    $request->getBaseUrl().$pathInfo.($params ? '?'.http_build_query($params) : ''),
                    LocaleManager::HTTP_REDIRECT_PERM
                )
            );

            return;
        }
        //locale was not found

        $locale = $this->localeManager->getLocale($request, $allowedLocales);
        $request->setLocale($locale);
        $event->setResponse(
            $this->localeManager->redirect($request->getBaseUrl(), $pathInfo, $locale, $params)
        );
    }




}