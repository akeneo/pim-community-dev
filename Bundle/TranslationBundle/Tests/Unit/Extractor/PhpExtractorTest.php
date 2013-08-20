<?php

namespace Oro\Bundle\TranslationBundle\Tests\Unit\Extractor;

use Oro\Bundle\TranslationBundle\Tests\Unit\Fixtures\SomeClass;
use Symfony\Component\Translation\MessageCatalogue;

use Oro\Bundle\TranslationBundle\Extractor\PhpExtractor;

class PhpExtractorTest extends \PHPUnit_Framework_TestCase
{
    const MESSAGE_FROM_VARIABLE = 'oro.translation.some_another_string';

    public function testExtraction()
    {
        // Arrange
        $extractor = new PhpExtractor();
        $extractor->setPrefix('prefix');
        $catalogue = new MessageCatalogue('en');

        // Act
        $extractor->extract(__DIR__.'/../Fixtures', $catalogue);

        // Assert
        $this->assertCount(2, $catalogue->all('messages'), '->extract() should find 2 translation');
        $this->assertTrue($catalogue->has(SomeClass::STRING_TO_TRANSLATE), '->extract() should extract constants');
        $this->assertTrue($catalogue->has(self::MESSAGE_FROM_VARIABLE), '->extract() should extract variables');
        $this->assertFalse(
            $catalogue->has(SomeClass::STRING_NOT_TO_TRANSLATE),
            '->extract() should not translate messages that start not with vendor name'
        );
        $this->assertEquals(
            'prefix' . SomeClass::STRING_TO_TRANSLATE,
            $catalogue->get(SomeClass::STRING_TO_TRANSLATE),
            '->extract() should apply "prefix" as prefix'
        );
    }
}
