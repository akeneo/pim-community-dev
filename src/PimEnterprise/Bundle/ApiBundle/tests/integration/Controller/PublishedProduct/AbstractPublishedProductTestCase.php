<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\PublishedProduct;

use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Pim\Component\Catalog\tests\integration\Normalizer\NormalizedProductCleaner;
use PimEnterprise\Component\Workflow\Model\PublishedProductInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
abstract class AbstractPublishedProductTestCase extends ApiTestCase
{
    /**
     * @param string $identifier
     * @param array  $data
     *
     * @return PublishedProductInterface
     */
    protected function createPublishedProduct($identifier, array $data = [])
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->get('akeneo_elasticsearch.client')->refreshIndex();

        $published = $this->get('pimee_workflow.manager.published_product')->publish($product);

        return $published;
    }

    /**
     * @param Response $response
     * @param string   $expected
     */
    protected function assertListResponse(Response $response, $expected)
    {
        $result = json_decode($response->getContent(), true);
        $expected = json_decode($expected, true);

        if (!isset($result['_embedded'])) {
            \PHPUnit_Framework_Assert::fail($response->getContent());
        }

        foreach ($result['_embedded']['items'] as $index => $product) {
            NormalizedProductCleaner::clean($result['_embedded']['items'][$index]);

            if (isset($expected['_embedded']['items'][$index])) {
                NormalizedProductCleaner::clean($expected['_embedded']['items'][$index]);
            }
        }

        $this->assertEquals($expected, $result);
    }
}
