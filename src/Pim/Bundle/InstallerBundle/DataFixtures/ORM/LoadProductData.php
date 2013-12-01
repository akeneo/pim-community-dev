<?php

namespace Pim\Bundle\InstallerBundle\DataFixtures\ORM;

use Symfony\Component\Yaml\Yaml;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Model\Product;
use Pim\Bundle\ImportExportBundle\Processor\ProductProcessor;

/**
 * Load fixtures for products
 *
 * @author    Nicolas Dupont <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LoadProductData extends AbstractInstallerFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $configuration = Yaml::parse(realpath($this->getFilePath()));
        $processor = $this->getProductProcessor();
        if (isset($configuration['products'])) {
            foreach ($configuration['products'] as $data) {
                $product = $processor->process($data);
                $this->validate($product, $data);
                $manager->persist($product);
            }
            $manager->flush();
        }
    }

    /**
     * Get product processor
     *
     * @return ProductProcessor
     */
    protected function getProductProcessor()
    {
        return $this->container->get('pim_import_export.processor.product');
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'products';
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 180;
    }
}
