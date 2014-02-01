<?php

namespace TWI\MultiDomainLocaleBundle\Tests\Functional\TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/", name = "home")
     * @Template("TestBundle:Default:index.html.twig")
     */
    public function index0Action(Request $request)
    {
        $locale = method_exists($request, 'getLocale') ? $request->getLocale()
            : $request->getSession()->getLocale();

        return array('locale' => $locale);
    }

    /**
     * @Route("/login", name = "homepage")
     * @Template("TestBundle:Default:index.html.twig")
     */
    public function index1Action(Request $request)
    {
        $locale = method_exists($request, 'getLocale') ? $request->getLocale()
            : $request->getSession()->getLocale();

        return array('locale' => $locale);
    }

    /**
     * @Route("/{_locale}/login", name = "homepage2")
     * @Template("TestBundle:Default:index.html.twig")
     */
    public function index2Action(Request $request)
    {
        $locale = method_exists($request, 'getLocale') ? $request->getLocale()
            : $request->getSession()->getLocale();

        return array('locale' => $locale);
    }

    /**
     * @Route("/v3/version", name = "homepage3")
     * @Template("TestBundle:Default:index.html.twig")
     */
    public function index3Action(Request $request)
    {
        $locale = method_exists($request, 'getLocale') ? $request->getLocale()
            : $request->getSession()->getLocale();

        return array('locale' => $locale);
    }

    /**
     * @Route("/{_locale}/v3/version", name = "homepage4")
     * @Template("TestBundle:Default:index.html.twig")
     */
    public function index4Action(Request $request)
    {
        $locale = method_exists($request, 'getLocale') ? $request->getLocale()
            : $request->getSession()->getLocale();

        return array('locale' => $locale);
        #throw $this->createNotFoundException('message');
    }

}