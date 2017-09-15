<?php

namespace Pim\Bundle\VersioningBundle\tests\integration\EventSubscriber;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\Common\Util\ClassUtils;

/**
 * @author    Axel Ducret <axel.ducret@synolia.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddVersionSubscriberIntegration extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration([Configuration::getTechnicalSqlCatalogPath()]);
    }

    public function testCreateAndUpdateProduct()
    {
        $productSaver = $this->get('pim_catalog.saver.product');

        $product = $this->get('pim_catalog.builder.product')->createProduct('product');

        $productSaver->save($product);

        $this->get('pim_catalog.updater.product')->update(
            $product,
            [
                'values' => [
                    'a_text' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 'New text',
                        ],
                    ],
                ],
            ]
        );

        $productSaver->save($product);

        $versions    = $this->get('pim_versioning.manager.version')->getLogEntries($product);
        $lastVersion = end($versions);

        $this->assertCount(2, $versions);

        $this->assertNotNull($lastVersion->getLoggedAt());
        $this->assertEquals($lastVersion->getResourceName(), ClassUtils::getClass($product));
        $this->assertEquals($lastVersion->getResourceId(), $product->getId());
        $this->assertEquals($lastVersion->getVersion(), 2);
        $this->assertEquals($lastVersion->getSnapshot(), [
            'sku'        => 'product',
            'family'     => '',
            'groups'     => '',
            'categories' => '',
            'a_text'     => 'New text',
            'enabled'    => 1,
        ]);
        $this->assertEquals($lastVersion->getChangeset(), [
            'a_text' => [
                'old' => '',
                'new' => 'New text',
            ],
        ]);
    }
}
