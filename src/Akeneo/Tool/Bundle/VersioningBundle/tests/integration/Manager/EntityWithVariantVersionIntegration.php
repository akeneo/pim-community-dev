<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\VersioningBundle\tests\integration\Manager;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\Common\Util\ClassUtils;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;

/**
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntityWithVariantVersionIntegration extends TestCase
{
    /**
     * Ensure the different versions contains only the entity data and not the
     * ones of its parent.
     */
    public function testProductModelAndVariantProductVersions(): void
    {
        $apollon = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('apollon');
        $oldName = null === $apollon->getValue('name', 'en_US')
            ? ''
            : $apollon->getValue('name', 'en_US')->getData();

        $this->updateProductModel($apollon, [
            'values' => [
                'name' => [
                    [
                        'locale' => 'en_US',
                        'scope' => null,
                        'data' => 'A new model name',
                    ],
                ],
            ],
        ]);

        $apollonBlue = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('apollon_blue');
        $oldComposition = null === $apollonBlue->getValue('composition')
            ? ''
            : $apollonBlue->getValue('composition')->getData();

        $this->updateProductModel($apollonBlue, [
            'values' => [
                'composition' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'A new composition',
                    ],
                ],
            ],
        ]);

        $apollonBlueXXL = $this->get('pim_catalog.repository.product')->findOneByIdentifier('1111111119');
        $oldEAN = null === $apollonBlueXXL->getValue('ean')
            ? ''
            : $apollonBlueXXL->getValue('ean')->getData();

        $this->updateVariantProduct($apollonBlueXXL, [
            'values' => [
                'ean' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => '1234567890131666',
                    ],
                ],
            ],
        ]);

        $this->assertVersions($apollon, 'name-en_US', $oldName, 'A new model name');
        $this->assertVersions($apollonBlue, 'composition', $oldComposition, 'A new composition');
        $this->assertVersions($apollonBlueXXL, 'ean', $oldEAN, '1234567890131666');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    /**
     * @param EntityWithFamilyVariantInterface $entity
     * @param string                           $field
     * @param string                           $oldData
     * @param string                           $newData
     */
    private function assertVersions(
        EntityWithFamilyVariantInterface $entity,
        string $field,
        string $oldData,
        string $newData
    ): void {
        $versions = $this->get('pim_versioning.repository.version')->getLogEntries(
            ClassUtils::getClass($entity),
            $entity->getId()
        );
        $this->assertSame(2, count($versions));

        $lastVersion = $this->get('pim_versioning.repository.version')->getNewestLogEntry(
            ClassUtils::getClass($entity),
            $entity->getId()
        );
        $changeSet = $lastVersion->getChangeset();
        $this->assertSame(1, count($changeSet));
        $this->assertSame($changeSet[$field]['old'], $oldData);
        $this->assertSame($changeSet[$field]['new'], $newData);
    }

    /**
     * Each time we create a product model, a batch job is ran to calculate the
     * completeness of its descendants.
     *
     * This is done by a batch job, and if several product models are created one
     * after the other, we can end up with a MySQL error because several jobs run
     * at the same time.
     *
     * Here, we use `akeneo_integration_tests.doctrine.job_execution` to be sure
     * the batch jobs are done running before continuing the test.
     *
     * @param ProductModelInterface $productModel
     * @param array                 $data
     */
    private function updateProductModel(ProductModelInterface $productModel, array $data): void
    {
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);

        $errors = $this->get('pim_catalog.validator.product_model')->validate($productModel);
        $this->assertEquals(0, $errors->count());

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        $launcher = $this->get('akeneo_integration_tests.launcher.job_launcher');

        while ($launcher->hasJobInQueue()) {
            $launcher->launchConsumerOnce();
        }
    }

    /**
     * @param ProductInterface $variantProduct
     * @param array            $data
     */
    private function updateVariantProduct(ProductInterface $variantProduct, array $data): void
    {
        $this->get('pim_catalog.updater.product')->update($variantProduct, $data);

        $errors = $this->get('pim_catalog.validator.product')->validate($variantProduct);
        $this->assertEquals(0, $errors->count());

        $this->get('pim_catalog.saver.product')->save($variantProduct);
    }
}
