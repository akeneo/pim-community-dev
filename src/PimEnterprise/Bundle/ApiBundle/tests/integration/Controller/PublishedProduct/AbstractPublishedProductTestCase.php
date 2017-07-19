<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\PublishedProduct;

use PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\Product\AbstractProductTestCase;
use PimEnterprise\Component\Workflow\Model\PublishedProductInterface;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
abstract class AbstractPublishedProductTestCase extends AbstractProductTestCase
{
    /**
     * @param string $identifier
     *
     * @return PublishedProductInterface
     */
    protected function publishProduct($product)
    {
        $published = $this->get('pimee_workflow.manager.published_product')->publish($product);

        return $published;
    }
}
