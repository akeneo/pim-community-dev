<?php

namespace Pim\Bundle\DemoBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\FlexibleEntityBundle\Entity\Price;
use Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\Product;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\Entity\ProductPrice;
use Oro\Bundle\BatchBundle\Entity\JobInstance;

/**
 * Load jobs
 *
 * Execute with "php app/console doctrine:fixtures:load"
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LoadJobInstanceData extends AbstractDemoFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $jobInstance = $this->createJobInstance(
            'pe',
            'Product export',
            'product_export',
            'Akeneo CSV Connector',
            'export',
            array(
                'pim_import_export.jobs.product_export.export.title' => array(
                    'reader' => array(
                        'channel' => 'ecommerce',
                    ),
                    'processor' => array(
                        'delimiter'  => ';',
                        'enclosure'  => '"',
                        'withHeader' => true,
                    ),
                    'writer' => array(
                        'path' => '/tmp/export.csv',
                    )
                )
            )
        );
        $manager->persist($jobInstance);

        $jobInstance = $this->createJobInstance(
            'pi',
            'Product import',
            'product_import',
            'Akeneo CSV Connector',
            'import',
            array(
                'pim_import_export.jobs.product_import.import.title' => array(
                    'reader' => array(
                        'filePath'    => '/tmp/export.csv',
                        'delimiter'   => ';',
                        'enclosure'   => '"',
                        'escape'      => '\\',
                        'allowUpload' => false,
                    ),
                    'processor' => array(
                        'enabled'          => true,
                        'categoriesColumn' => 'categories',
                        'familyColumn'     => 'family',
                        'channel'          => 'ecommerce'
                    ),
                    'writer' => array()
                )
            )
        );
        $manager->persist($jobInstance);
        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 160;
    }

    private function createJobInstance($code, $label, $alias, $connector, $type, $rawConfiguration)
    {
        $jobInstance = new JobInstance($connector, $type, $alias);
        $jobInstance->setCode($code);
        $jobInstance->setLabel($label);
        $jobInstance->setType($type);
        $jobInstance->setRawConfiguration($rawConfiguration);

        return $jobInstance;
    }
}
