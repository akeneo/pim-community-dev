<?php

namespace Oro\Bundle\NavigationBundle\Twig;

use Oro\Bundle\NavigationBundle\Provider\TitleServiceInterface;

class TitleExtension extends \Twig_Extension
{
    const EXT_NAME = 'oro_title';

    /**
     * @var TitleServiceInterface
     */
    protected $titleService;

    /**
     * @var array
     */
    protected $templateFileTitleDataStack = array();

    /**
     * @param TitleServiceInterface $titleService
     */
    public function __construct(TitleServiceInterface $titleService)
    {
        $this->titleService = $titleService;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'oro_title_render' => new \Twig_Function_Method($this, 'render'),
            'oro_title_render_short' => new \Twig_Function_Method($this, 'renderShort'),
            'oro_title_render_serialized' => new \Twig_Function_Method($this, 'renderSerialized'),
        );
    }

    /**
     * Register new token parser
     *
     * @return array
     */
    public function getTokenParsers()
    {
        return array(
            new TitleSetTokenParser()
        );
    }

    /**
     * Renders title
     *
     * @param null $titleData
     * @return string
     */
    public function render($titleData = null)
    {
        return $this->titleService
            ->setData($this->getTitleData())
            ->render(array(), $titleData, null, null, true);
    }

    /**
     * Renders short title
     *
     * @param null $titleData
     * @return string
     */
    public function renderShort($titleData = null)
    {
        return $this->titleService
            ->setData($this->getTitleData())
            ->render(array(), $titleData, null, null, true, true);
    }

    /**
     * Returns json serialized data
     *
     * @return string
     */
    public function renderSerialized()
    {
        return $this->titleService->setData($this->getTitleData())->getSerialized();
    }

    /**
     * Set title options.
     *
     * Options of all calls from template files will be merged in reverse order and set to title service before
     * rendering. Options from children templates will override with parents. This approach is required to implement
     * extend behavior of oro_title_render_* functions in templates, because by default in Twig children templates
     * are executed first.
     *
     * @param array $options
     * @param string|null $templateScope
     * @return TitleExtension
     */
    public function set(array $options = array(), $templateScope = null)
    {
        $this->addTitleData($options, $templateScope);
        return $this;
    }

    /**
     * @param array $options
     * @param string|null $templateScope
     */
    protected function addTitleData(array $options = array(), $templateScope = null)
    {
        if (!$templateScope) {
            $backtrace = debug_backtrace(false);
            if (!empty($backtrace[1]['file'])) {
                $templateScope = md5($backtrace[1]['file']);
            } else {
                $templateScope = md5(uniqid('twig_title', true)); // random string
            }
        }

        if (!isset($this->templateFileTitleDataStack[$templateScope])) {
            $this->templateFileTitleDataStack[$templateScope] = array();
        }
        $this->templateFileTitleDataStack[$templateScope][] = $options;
    }

    /**
     * @return array
     */
    protected function getTitleData()
    {
        $result = array();
        if ($this->templateFileTitleDataStack) {
            $result = array();
            foreach (array_reverse($this->templateFileTitleDataStack) as $templateOptions) {
                foreach ($templateOptions as $options) {
                    $result = array_merge($result, $options);
                }
            }
        }
        return $result;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return self::EXT_NAME;
    }
}
