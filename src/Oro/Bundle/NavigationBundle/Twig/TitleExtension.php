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
    protected $templateFileTitleDataStack = [];

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
        return [
            'oro_title_render' => new \Twig_Function_Method($this, 'render')
        ];
    }

    /**
     * Register new token parser
     *
     * @return array
     */
    public function getTokenParsers()
    {
        return [
            new TitleSetTokenParser()
        ];
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
            ->render([], $titleData, null, null, true);
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
    public function set(array $options = [], $templateScope = null)
    {
        $this->addTitleData($options, $templateScope);
        return $this;
    }

    /**
     * @param array $options
     * @param string|null $templateScope
     */
    protected function addTitleData(array $options = [], $templateScope = null)
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
            $this->templateFileTitleDataStack[$templateScope] = [];
        }
        $this->templateFileTitleDataStack[$templateScope][] = $options;
    }

    /**
     * @return array
     */
    protected function getTitleData()
    {
        $result = [];
        if ($this->templateFileTitleDataStack) {
            $result = [];
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
