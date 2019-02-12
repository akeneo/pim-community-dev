<?php

namespace AkeneoTest\Pim\Structure\EndToEnd\FamilyVariant\ExternalApi;

use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Symfony\Component\HttpFoundation\Response;

class GetFamilyVariantEndToEnd extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createFamily([
            'code'        => 'familyB',
            'attributes'  => ['sku', 'a_simple_select', 'a_yes_no'],
            'attribute_requirements' => [
                'tablet' => ['sku', 'a_simple_select', 'a_yes_no']
            ]
        ]);

        $this->createFamilyVariant([
            'code'        => 'variantFamilyB',
            'family'      => 'familyB',
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['a_simple_select'],
                    'attributes' => ['a_simple_select', 'a_yes_no', 'sku'],
                ],
            ]
        ]);
    }

    public function testGetAFamilyVariant()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/families/familyA/variants/familyVariantA1');
        $expected = <<<JSON
{
    "code" : "familyVariantA1",
    "variant_attribute_sets" : [
        {
            "level" : 1,
            "attributes" : [
                "a_simple_select",
                "a_text"
            ],
            "axes" : [
                "a_simple_select"
            ]
        },
        {
            "level" : 2,
            "attributes" : [
                "sku",
                "a_text_area",
                "a_yes_no"
            ],
            "axes" : [
                "a_yes_no"
            ]
        }
    ],
    "labels" : {}
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testNotFoundFamily()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/families/unknownFamily/variants/familyVariantA1');
        $expected = <<<JSON
{
    "code" : 404,
    "message" : "Family \"unknownFamily\" does not exist."
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testNotFoundFamilyVariant()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/families/familyA/variants/unknownFamilyVariant');
        $expected = <<<JSON
{
    "code" : 404,
    "message" : "Family variant \"unknownFamilyVariant\" does not exist or is not a variant of the family \"familyA\"."
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testFamilyVariantNotAssociatedToTheFamily()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/families/familyA/variants/variantFamilyB');
        $expected = <<<JSON
{
    "code" : 404,
    "message" : "Family variant \"variantFamilyB\" does not exist or is not a variant of the family \"familyA\"."
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    /**
     * @param array $data
     *
     * @return FamilyInterface
     */
    protected function createFamily(array $data = []): FamilyInterface
    {
        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update($family, $data);
        $constraintList = $this->get('validator')->validate($family);
        $this->assertEquals(0, $constraintList->count());
        $this->get('pim_catalog.saver.family')->save($family);

        return $family;
    }

    /**
     * @param array $data
     *
     * @return FamilyVariantInterface
     */
    protected function createFamilyVariant(array $data = []): FamilyVariantInterface
    {
        $family = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update($family, $data);
        $constraintList = $this->get('validator')->validate($family);
        $this->assertEquals(0, $constraintList->count());
        $this->get('pim_catalog.saver.family_variant')->save($family);

        return $family;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
