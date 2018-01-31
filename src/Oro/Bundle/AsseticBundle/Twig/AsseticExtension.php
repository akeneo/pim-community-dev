<?php

namespace Oro\Bundle\AsseticBundle\Twig;

use Oro\Bundle\AsseticBundle\Parser\AsseticTokenParser;
use Symfony\Bundle\AsseticBundle\Factory\AssetFactory;
use Symfony\Bundle\AsseticBundle\Twig\AsseticNodeVisitor;
use Symfony\Component\Templating\TemplateNameParserInterface;

class AsseticExtension extends \Twig_Extension
{
    /**
     * @var \Symfony\Bundle\AsseticBundle\Factory\AssetFactory
     */
    protected $assetsFactory;

    /**
     * @var array
     */
    protected $assets;

    /**
     * @var \Symfony\Component\Templating\TemplateNameParserInterface
     */
    protected $templateNameParser;

    /**
     * @var array
     */
    protected $enabledBundles;

    /**
     * @param AssetFactory                $assetsFactory
     * @param array                       $assets
     * @param TemplateNameParserInterface $templateNameParser
     * @param array                       $enabledBundles
     */
    public function __construct(
        AssetFactory $assetsFactory,
        array $assets,
        TemplateNameParserInterface $templateNameParser,
        $enabledBundles = []
    ) {
        $this->enabledBundles = $enabledBundles;
        $this->templateNameParser = $templateNameParser;
        $this->assetsFactory = $assetsFactory;
        $this->assets = $assets;
    }

    /**
     * {@inheritDoc}
     */
    public function getTokenParsers()
    {
        return [
            new AsseticTokenParser($this->assets['css'], $this->assetsFactory, 'oro_css', 'css/*.css'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getNodeVisitors()
    {
        return [
            new AsseticNodeVisitor($this->templateNameParser, $this->enabledBundles),
        ];
    }
}
