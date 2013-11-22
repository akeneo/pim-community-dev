<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Processor;

use Pim\Bundle\ImportExportBundle\Processor\ProductAssociationProcessor;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAssociationProcessorTest extends AbstractProcessorTestCase
{
    /**
     * @var ProductManager
     */
    protected $productManager;

    /**
     * {@inheritdoc}
     */
    protected function createProcessor()
    {
        $this->productManager = $this->mock('Pim\Bundle\CatalogBundle\Manager\ProductManager');

        return new ProductAssociationProcessor($this->em, $this->validator, $this->productManager);
    }

    /**
     * {@inheritdoc}
     */
    protected function getExpectedConfigurationFields()
    {
        return array();
    }
}
