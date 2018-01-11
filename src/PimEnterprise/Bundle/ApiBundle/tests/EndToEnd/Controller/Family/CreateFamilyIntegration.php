<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ApiBundle\tests\EndToEnd\Controller\Family;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use PimEnterprise\Bundle\ProductAssetBundle\AttributeType\AttributeTypes;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class CreateFamilyIntegration extends ApiTestCase
{
    /**
     * @test
     */
    public function create_a_family_with_an_asset_as_attribute_as_image()
    {
        $client = $this->createAuthenticatedClient();

        $data = <<<JSON
{
    "code": "a_family",
    "attributes": ["an_asset", "a_metric", "a_price"],
    "attribute_as_label": "sku",
    "attribute_as_image": "an_asset"
}
JSON;

        $client->request('POST', 'api/rest/v1/families', [], [], [], $data);

        $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier('a_family');
        $familyStandard = [
            'code' => 'a_family',
            'attributes' => ['a_metric', 'a_price', 'an_asset', 'sku'],
            'attribute_as_label' => 'sku',
            'attribute_as_image' => 'an_asset',
            'attribute_requirements' => [
                'ecommerce' => ['sku'],
                'ecommerce_china' => ['sku'],
                'tablet' => ['sku'],
            ],
            'labels' => [],
        ];

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame(
            $familyStandard,
            $this->get('pim_catalog.normalizer.standard.family')->normalize($family)
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    protected function setUp()
    {
        parent::setUp();

        $attribute = $this->get('pim_catalog.factory.attribute')->createAttribute(AttributeTypes::ASSETS_COLLECTION);

        $this->get('pim_catalog.updater.attribute')->update(
            $attribute,
            [
                'code' => 'an_asset',
                'reference_data_name' => 'assets',
                'group' => 'other',
            ]
        );

        $this->assertCount(0, $this->get('validator')->validate($attribute));

        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }
}
