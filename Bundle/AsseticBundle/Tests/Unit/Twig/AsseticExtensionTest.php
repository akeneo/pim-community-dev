<?php
namespace Oro\Bundle\NavigationBundle\Tests\Unit\Twig;

use Oro\Bundle\AsseticBundle\Twig\AsseticExtension;

class AsseticExtensionTest extends \PHPUnit_Framework_TestCase
{
    private $assetsFactory;
    private $assets;
    private $templateNameParser;
    private $enabledBundles;

    /**
     * @var \Oro\Bundle\NavigationBundle\Twig\AsseticExtension
     */
    private $extension;

    public function setUp()
    {
        $this->assetsFactory = $this->getMockBuilder('Symfony\Bundle\AsseticBundle\Factory\AssetFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->templateNameParser = $this->getMockBuilder('Symfony\Component\Templating\TemplateNameParserInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assets = array(
            'css' => array(
                'first.css',
                'second.css'
            ),
            'js' => array(
                'first.js',
                'second.js'
            )
        );

        $this->enabledBundles = array('testBundle');

        $this->extension = new AsseticExtension(
            $this->assetsFactory,
            $this->assets,
            $this->templateNameParser,
            $this->enabledBundles
        );
    }

    public function testGetTokenParsers()
    {
        $tokens = $this->extension->getTokenParsers();
        $this->assertTrue(is_array($tokens));
        $jsToken = $tokens[0];
        $this->assertEquals('oro_js', $jsToken->getTag());
    }

    public function testGetName()
    {
        $this->assertEquals('oro_assetic', $this->extension->getName());
    }

    public function testGetNodeVisitors()
    {
        $asseticNodeVisitors = $this->extension->getNodeVisitors();
        $this->assertTrue(is_array($asseticNodeVisitors));
    }
}
