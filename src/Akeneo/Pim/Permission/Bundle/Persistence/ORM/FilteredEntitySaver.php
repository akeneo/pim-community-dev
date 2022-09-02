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

namespace Akeneo\Pim\Permission\Bundle\Persistence\ORM;

use Akeneo\Pim\Permission\Component\NotGrantedDataMergerInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Persistence\ObjectRepository;

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

    /** @var NotGrantedDataMergerInterface */
    private $mergeDataOnEntity;

    /** @var ObjectRepository */
    private $entityRepository;

    /**
     * @param SaverInterface                $saver
     * @param BulkSaverInterface            $bulkSaver
     * @param NotGrantedDataMergerInterface $mergeDataOnEntity
     * @param ObjectRepository              $entityRepository
     */
    public function __construct(
        SaverInterface $saver,
        BulkSaverInterface $bulkSaver,
        NotGrantedDataMergerInterface $mergeDataOnEntity,
        ObjectRepository $entityRepository
    ) {
        $this->saver = $saver;
        $this->bulkSaver = $bulkSaver;
        $this->mergeDataOnEntity = $mergeDataOnEntity;
        $this->entityRepository = $entityRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function save($filteredEntity, array $options = [])
    {
        $fullEntity = $this->getFullEntity($filteredEntity);

        $this->saver->save($fullEntity, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $filteredEntities, array $options = [])
    {
        $fullEntities = [];

        foreach ($filteredEntities as $filteredEntity) {
            $fullEntities[] = $this->getFullEntity($filteredEntity);
        }

        $this->bulkSaver->saveAll($fullEntities, $options);
    }

    /**
     * $filteredEntity is the entity with only granted data.
     * To avoid to lose data, we have to send to the saver the full entity with all data (included not granted).
     * To do that, we get the entity from the DB and merge new data from $filteredEntity into this entity.
     *
     * @param mixed $filteredEntity
     *
     * @return mixed
     */
    private function getFullEntity($filteredEntity)
    {
        if ($filteredEntity->isNew()) {
            return $this->mergeDataOnEntity->merge($filteredEntity);
        }

        if (method_exists($filteredEntity, 'getUuid')) {
            $fullEntity = $this->entityRepository->find($filteredEntity->getUuid());
        } else {
            $fullEntity = $this->entityRepository->find($filteredEntity->getId());
        }

        return $this->mergeDataOnEntity->merge($filteredEntity, $fullEntity);
    }
}
