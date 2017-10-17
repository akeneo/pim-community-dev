<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogBundle\Security\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use PimEnterprise\Component\Catalog\Security\Applier\ApplierInterface;

/**
 * Before saving a filtered entity, we need to merge not granted data into this entity to avoid to lose data.
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class FilteredEntitySaver implements SaverInterface, BulkSaverInterface
{
    /** @var SaverInterface */
    private $saver;

    /** @var BulkSaverInterface */
    private $bulkSaver;

    /** @var ApplierInterface */
    private $applyDataOnProduct;

    /**
     * @param SaverInterface     $saver
     * @param BulkSaverInterface $bulkSaver
     * @param ApplierInterface   $applyDataOnProduct
     */
    public function __construct(
        SaverInterface $saver,
        BulkSaverInterface $bulkSaver,
        ApplierInterface $applyDataOnProduct
    ) {
        $this->saver = $saver;
        $this->bulkSaver = $bulkSaver;
        $this->applyDataOnProduct = $applyDataOnProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function save($filteredEntity, array $options = [])
    {
        $this->saver->save($this->applyDataOnProduct->apply($filteredEntity), $options);
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $filteredEntities, array $options = [])
    {
        $fullEntities = [];

        foreach ($filteredEntities as $filteredEntity) {
            $fullEntities[] = $this->applyDataOnProduct->apply($filteredEntity);
        }

        $this->bulkSaver->saveAll($fullEntities, $options);
    }
}
