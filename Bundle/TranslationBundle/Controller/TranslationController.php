<?php
namespace Oro\Bundle\TranslationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TranslationController extends Controller
{
    const JS_TRANSLATION_TEMPLATE = 'OroTranslationBundle:Translation:translation.js.twig';

    public function indexAction(Request $request, $_locale)
    {
        $options = $this->container->getParameter('oro_translation.js_translation');
        $domainsTranslations = $this->get('translator')->getTranslations($options['domains'], $_locale);

        $result = array(
            'locale'         => $_locale,
            'defaultDomains' => $options['domains'],
            'messages'       => array(),
        );
        if ($options['debug']) {
            $result['debug'] = true;
        }

        foreach ($domainsTranslations as $domain => $translations) {
            $result['messages'] += array_combine(array_map(function($id) use ($domain) {
                return sprintf('%s:%s', $domain, $id);
            }, array_keys($translations)), array_values($translations));
        }

        return $this->render(self::JS_TRANSLATION_TEMPLATE, array(
            'json' => $result
        ), new Response('', 200, array('Content-Type' => $request->getMimeType('js'))));
    }
}
