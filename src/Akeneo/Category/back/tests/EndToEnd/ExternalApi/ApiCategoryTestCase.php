<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Test\Category\EndToEnd\ExternalApi;

use Akeneo\Category\Domain\ValueObject\Attribute\Value\AbstractValue;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;

abstract class ApiCategoryTestCase extends ApiTestCase
{
    protected function activateEnrichedFeatureFlag(): void
    {
        $featureFlags = $this->get('feature_flags');
        $featureFlags->enable('enriched_category');
    }

    protected function updateCategoryWithValues(string $code): void
    {
        $query = <<<SQL
UPDATE pim_catalog_category SET value_collection = :value_collection WHERE code = :code;
SQL;

        $this->get('database_connection')->executeQuery($query, [
            'value_collection' => json_encode([
                'attribute_codes' => [
                    'title'.AbstractValue::SEPARATOR.'87939c45-1d85-4134-9579-d594fff65030',
                    'photo'.AbstractValue::SEPARATOR.'8587cda6-58c8-47fa-9278-033e1d8c735c',
                ],
                'title'.AbstractValue::SEPARATOR.'87939c45-1d85-4134-9579-d594fff65030'.AbstractValue::SEPARATOR.'ecommerce'.AbstractValue::SEPARATOR.'en_US' => [
                    'data' => 'All the shoes you need!',
                    'type' => 'text',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                    'attribute_code' => 'title'.AbstractValue::SEPARATOR.'87939c45-1d85-4134-9579-d594fff65030',
                ],
                'title'.AbstractValue::SEPARATOR.'87939c45-1d85-4134-9579-d594fff65030'.AbstractValue::SEPARATOR.'ecommerce'.AbstractValue::SEPARATOR.'fr_FR' => [
                    'data' => 'Les chaussures dont vous avez besoin !',
                    'type' => 'text',
                    'channel' => 'ecommerce',
                    'locale' => 'fr_FR',
                    'attribute_code' => 'title'.AbstractValue::SEPARATOR.'87939c45-1d85-4134-9579-d594fff65030',
                ],
                'photo'.AbstractValue::SEPARATOR.'8587cda6-58c8-47fa-9278-033e1d8c735c' => [
                    'data' => [
                        'size' => 168107,
                        'extension' => 'jpg',
                        'file_path' => '8/8/3/d/883d041fc9f22ce42fee07d96c05b0b7ec7e66de_shoes.jpg',
                        'mime_type' => 'image/jpeg',
                        'original_filename' => 'shoes.jpg',
                    ],
                    'type' => 'image',
                    'channel' => null,
                    'locale' => null,
                    'attribute_code' => 'photo'.AbstractValue::SEPARATOR.'8587cda6-58c8-47fa-9278-033e1d8c735c',
                ],
            ], JSON_THROW_ON_ERROR),
            'code' => $code,
        ]);
    }
}
