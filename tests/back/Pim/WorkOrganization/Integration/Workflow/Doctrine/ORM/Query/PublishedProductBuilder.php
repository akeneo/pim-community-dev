<?php


namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Workflow\Doctrine\ORM\Query;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
trait PublishedProductBuilder {

    public function createPublishedProduct(string $identifier, array $data): PublishedProductInterface
    {
        $product = $this->createProduct($identifier, $data);
        return $this->get('pimee_workflow.manager.published_product')->publish($product);
    }

    public function createProduct(string $identifier, array $data): ProductInterface  {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier, 'familyA2');

        $data = array_merge([
            'values'     => [
                'a_metric' => [
                    ['data' => ['amount' => 1, 'unit' => 'WATT'], 'locale' => null, 'scope' => null]
                ],
            ]
        ], $data);

        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->get('pim_catalog.saver.product')->save($product);
        return $product;
    }

}
