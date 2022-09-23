<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Syndication\Test\Acceptance\Hydrator\Value\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\FileValue;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;

class FileValueHydratorTest extends AbstractAttributeValueHydratorTest
{
    /**
     * @test
     */
    public function it_hydrates_a_file_value_from_product_value(): void
    {
        $fileInfo = new FileInfo();
        $fileInfo->setOriginalFilename('original_filename');
        $fileInfo->setStorage('a_storage');
        $fileInfo->setKey('a_key');

        $expectedValue = new FileValue(
            'product_identifier',
            'a_storage',
            'a_key',
            'original_filename',
            null,
            null,
        );

        $productValue = MediaValue::value('file_attribute_code', $fileInfo);

        $this->assertHydratedValueEquals($expectedValue, $productValue);
    }

    /**
     * @test
     */
    public function it_hydrates_a_file_value_from_localizable_and_scopable_product_value(): void
    {
        $fileInfo = new FileInfo();
        $fileInfo->setOriginalFilename('original_filename');
        $fileInfo->setStorage('a_storage');
        $fileInfo->setKey('a_key');

        $expectedValue = new FileValue(
            'product_identifier',
            'a_storage',
            'a_key',
            'original_filename',
            'ecommerce',
            'en_US',
        );

        $productValue = MediaValue::scopableLocalizableValue('file_attribute_code', $fileInfo, 'ecommerce', 'en_US');

        $this->assertHydratedValueEquals($expectedValue, $productValue);
    }

    protected function getAttributeType(): string
    {
        return 'pim_catalog_file';
    }
}
