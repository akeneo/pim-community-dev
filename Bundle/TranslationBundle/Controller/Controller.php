<?php
namespace Oro\Bundle\TranslationBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Controller
{
    /**
     * @var \Oro\Bundle\TranslationBundle\Translation\Translator
     */
    protected $translator;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Templating\DelegatingEngine
     */
    protected $templating;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var array
     */
    protected $options;

    /**
     * @param \Oro\Bundle\TranslationBundle\Translation\Translator $translator
     * @param \Symfony\Bundle\FrameworkBundle\Templating\DelegatingEngine $templating
     * @param string $template path to template
     * @param array $options array('domains' => array(), 'debug' => true|false)
     */
    public function __construct($translator, $templating, $template, $options)
    {
        $this->translator = $translator;
        $this->templating = $templating;
        $this->template = $template;
        $this->options = $options;
    }

    /**
     * Action point for js translation resource
     * @param Request $request
     * @param string $_locale
     * @return Response
     */
    public function indexAction(Request $request, $_locale)
    {
        $domains = $this->options['domains'];
        $debug = (bool)$this->options['debug'];

        $content = $this->renderJsTranslationContent($domains, $_locale, $debug);

        return new Response($content, 200, array('Content-Type' => $request->getMimeType('js')));
    }

    /**
     * Combines JSON with js translation and renders js-resource
     * @param array $domains
     * @param string $locale
     * @param bool $debug
     * @return string
     */
    public function renderJsTranslationContent($domains, $locale, $debug = false)
    {
        $domainsTranslations = $this->translator->getTranslations($domains, $locale);

        $result = array(
            'locale' => $locale,
            'defaultDomains' => $domains,
            'messages' => array(),
        );
        if ($debug) {
            $result['debug'] = true;
        }

        foreach ($domainsTranslations as $domain => $translations) {
            $result['messages'] += array_combine(
                array_map(
                    function ($id) use ($domain) {
                        return sprintf('%s:%s', $domain, $id);
                    },
                    array_keys($translations)
                ),
                array_values($translations)
            );
        }

        return $this->templating->render($this->template, array('json' => $result));
    }
}
