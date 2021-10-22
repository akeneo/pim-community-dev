<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\TableAttribute\EndToEnd\Attribute;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

abstract class AbstractAttributeApiTestCase extends ApiTestCase
{
    protected function createValidTableAttribute(Client $client)
    {
        $data =
            <<<JSON
    {
        "code":"a_table_attribute",
        "type":"pim_catalog_table",
        "group":"attributeGroupA",
        "table_configuration": [
            {
                "code": "ingredients",
                "data_type": "select",
                "labels": {
                    "en_US":"Ingredients",
                    "fr_FR":"Ingrédients"
                },
                "options": [{"code": "sugar", "labels": {"en_US": "Sugar", "fr_FR": "Sucre"}}]
            },
            {
                "code": "quantity",
                "data_type": "text",
                "validations": {
                    "max_length": 100
                }
            }
        ]
    }
JSON;

        $client->request('POST', 'api/rest/v1/attributes', [], [], [], $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
