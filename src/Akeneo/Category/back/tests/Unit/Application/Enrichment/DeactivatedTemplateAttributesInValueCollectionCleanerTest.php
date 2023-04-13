<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Unit\Application\Enrichment;

use Akeneo\Category\Application\Enrichment\DeactivatedTemplateAttributesInValueCollectionCleaner;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Query\DeactivatedTemplateAttributeIdentifier;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeactivatedTemplateAttributesInValueCollectionCleanerTest extends CategoryTestCase
{
    /**
     * @throws \JsonException
     */
    public function testItRemovesDeactivatedAttributesFromValueCollection(): void
    {
        $deactivatedAttributes = [
            new DeactivatedTemplateAttributeIdentifier('87939c45-1d85-4134-9579-d594fff65030', 'title')
        ];
        $rawCategory = [
            'id' => '1',
            'code' => 'master',
            'value_collection' => '{"photo|8587cda6-58c8-47fa-9278-033e1d8c735c":{"data":{"size":168107,"extension":"jpg","file_path":"shoes.jpg","mime_type":"jpeg","original_filename":"shoes.jpg"},"type":"image","locale":null,"channel":null,"attribute_code":"photo|8587cda6-58c8-47fa-9278-033e1d8c735c"},"title|87939c45-1d85-4134-9579-d594fff65030|ecommerce|en_US":{"data":"All the shoes you need!","type":"text","locale":"en_US","channel":"ecommerce","attribute_code":"title|87939c45-1d85-4134-9579-d594fff65030"},"title|87939c45-1d85-4134-9579-d594fff65030|ecommerce|fr_FR":{"data":"Les chaussures dont vous avez besoin !","type":"text","locale":"fr_FR","channel":"ecommerce","attribute_code":"title|87939c45-1d85-4134-9579-d594fff65030"}}',
        ];
        $expected = [
            'id' => '1',
            'code' => 'master',
            'value_collection' => '{"photo|8587cda6-58c8-47fa-9278-033e1d8c735c":{"data":{"size":168107,"extension":"jpg","file_path":"shoes.jpg","mime_type":"jpeg","original_filename":"shoes.jpg"},"type":"image","locale":null,"channel":null,"attribute_code":"photo|8587cda6-58c8-47fa-9278-033e1d8c735c"}}',
        ];

        $deactivatedAttributesCleaner = $this->get(DeactivatedTemplateAttributesInValueCollectionCleaner::class);
        $result = ($deactivatedAttributesCleaner)($deactivatedAttributes, $rawCategory);

        $this->assertEquals($expected, $result);
    }
}
