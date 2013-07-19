<?php
namespace Oro\Bundle\TranslationBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class Controller
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var EngineInterface
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
     * @param TranslatorInterface $translator
     * @param EngineInterface $templating
     * @param string $template path to template
     * @param array $options array('domains' => array(), 'debug' => true|false)
     */
    public function __construct(TranslatorInterface $translator, EngineInterface $templating, $template, $options)
    {
        $this->translator = $translator;
        $this->templating = $templating;
        $this->template = $template;
        $this->options = $options;
    }

    /**
     * Action point for js translation resource
     *
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
     *
     * @param array $domains
     * @param string $locale
     * @param bool $debug
     * @return string
     */
    public function renderJsTranslationContent(array $domains, $locale, $debug = false)
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
