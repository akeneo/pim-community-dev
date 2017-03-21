<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Product;

use Akeneo\Test\Integration\Configuration;
use Symfony\Component\HttpFoundation\Response;

class DeleteProductIntegration extends AbstractProductTestCase
{
    /**
     * @return Configuration
     */
    protected function getConfiguration()
    {
        return new Configuration(
            [Configuration::getTechnicalSqlCatalogPath()],
            false
        );
    }

    public function testDeleteAProduct()
    {
        $client = $this->createAuthenticatedClient();

        $this->assertCount(3, $this->get('pim_catalog.repository.product')->findAll());
        $this->assertProductRawValuesEquals(27);

        $client->request('DELETE', 'api/rest/v1/products/foo');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $this->assertCount(2, $this->get('pim_catalog.repository.product')->findAll());
        $this->assertProductRawValuesEquals(0);
        $this->assertNull($this->get('pim_catalog.repository.product')->findOneByIdentifier('foo'));
    }

    public function testNotFoundAProduct()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('DELETE', 'api/rest/v1/products/not_found');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content, 'response contains 2 items');
        $this->assertSame(Response::HTTP_NOT_FOUND, $content['code']);
        $this->assertSame('Product "not_found" does not exist.', $content['message']);
    }

    /**
     * @param int $number
     */
    private function assertProductRawValuesEquals($number)
    {
        $rawValues = $this
            ->get('pim_catalog.repository.product')
            ->createQueryBuilder('p')
            ->select('p.rawValues')
            ->getQuery()
            ->getArrayResult();

        $values = [];
        foreach ($rawValues as $rawValuesByProduct) {
            foreach ($rawValuesByProduct['rawValues'] as $rawValuesByProductAndAttribute) {
                foreach ($rawValuesByProductAndAttribute as $rawValuesByChannel) {
                    foreach ($rawValuesByChannel as $rawValueByChannelAndLocale) {
                        $values[] = $rawValueByChannelAndLocale;
                    }
                }
            }
        }

        $this->assertEquals($number, count($values));
    }
}
