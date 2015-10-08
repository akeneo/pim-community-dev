<?php

namespace Oro\Bundle\TranslationBundle\Tests\Unit\Extractor;

use Symfony\Component\Translation\MessageCatalogue;

use Oro\Bundle\TranslationBundle\Extractor\PhpCodeExtractor;
use Oro\Bundle\TranslationBundle\Tests\Unit\Fixtures\SomeClass;

class PhpCodeExtractorTest extends \PHPUnit_Framework_TestCase
{
    const MESSAGE_FROM_VARIABLE = 'oro.translation.some_another_string';
    const MESSAGE_FROM_ARGUMENT = 'vendor.bundle.type.message_string';

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $container;

    /** @var PhpCodeExtractor */
    protected $extractor;

    public function setUp()
    {
        $this->container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
            ->disableOriginalConstructor()->getMock();

        $this->extractor = new PhpCodeExtractor($this->container);
    }

    public function tearDown()
    {
        unset($this->container);
        unset($this->extractor);
    }

    public function testExtraction()
    {
        // Arrange
        $this->extractor->setPrefix('prefix');
        $catalogue = new MessageCatalogue('en');
        $this->container->expects($this->atLeastOnce())->method('has')
            ->will(
                $this->returnCallback(
                    function ($id) {
                        if ($id == SomeClass::STRING_NOT_TO_TRANSLATE) {
                            return true;
                        }

                        return false;
                    }
                )
            );

        // Act
        $this->extractor->extract(__DIR__ . '/../Fixtures/Resources/views', $catalogue);

        // Assert
        $this->assertCount(2, $catalogue->all('messages'), '->extract() should find 3 translations');
        $this->assertTrue($catalogue->has(SomeClass::STRING_TO_TRANSLATE), '->extract() should extract constants');
        $this->assertTrue($catalogue->has(self::MESSAGE_FROM_VARIABLE), '->extract() should extract variables');
        $this->assertFalse(
            $catalogue->has(self::MESSAGE_FROM_ARGUMENT),
            '->extract() should not extract messages from another vendor namespace'
        );
        $this->assertFalse(
            $catalogue->has(SomeClass::STRING_NOT_TO_TRANSLATE),
            '->extract() should not extract existed services'
        );
        $this->assertEquals(
            'prefix' . SomeClass::STRING_TO_TRANSLATE,
            $catalogue->get(SomeClass::STRING_TO_TRANSLATE),
            '->extract() should apply "prefix" as prefix'
        );
    }
}
