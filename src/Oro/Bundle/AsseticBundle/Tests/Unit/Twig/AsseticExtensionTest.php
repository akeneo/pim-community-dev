<?php
namespace Oro\Bundle\AsseticBundle\Tests\Unit\Twig;

use Oro\Bundle\AsseticBundle\Twig\AsseticExtension;

class AsseticExtensionTest extends \PHPUnit_Framework_TestCase
{
    private $assetsFactory;
    private $assets;
    private $templateNameParser;
    private $enabledBundles;

    /**
     * @var AsseticExtension
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
        $token = $tokens[0];
        $this->assertEquals('oro_css', $token->getTag());
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
