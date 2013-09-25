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
     * @param  null   $titleData
     * @return string
     */
    public function render($titleData = null)
    {
        return $this->titleService->render(array(), $titleData, null, null, true);
    }

    /**
     * Renders short title
     *
     * @param  null   $titleData
     * @return string
     */
    public function renderShort($titleData = null)
    {
        return $this->titleService->render(array(), $titleData, null, null, true, true);
    }

    /**
     * Set title options
     *
     * @param array $options
     * @return $this
     */
    public function set(array $options = array())
    {
        return $this->titleService->setData($options);
    }

    /**
     * Returns json serialized data
     *
     * @return string
     */
    public function renderSerialized()
    {
        return $this->titleService->getSerialized();
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
