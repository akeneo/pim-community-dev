<?php

namespace Oro\Bundle\UIBundle\Tests\Unit\Twig;

use Oro\Bundle\UIBundle\Twig\UiExtension;

class UiExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Oro\Bundle\UIBundle\Twig\UiExtension
     */
    protected $extension;

    public function setUp()
    {
        $this->extension = new UiExtension(array(), 'test_class');
    }

    public function testGetName()
    {
        $this->assertEquals('oro_ui', $this->extension->getName());
    }

    public function testGetTokenParsers()
    {
        $parsers = $this->extension->getTokenParsers();
        $this->assertTrue($parsers[0] instanceof \Oro\Bundle\UIBundle\Twig\Parser\PlaceholderTokenParser);
    }
}
