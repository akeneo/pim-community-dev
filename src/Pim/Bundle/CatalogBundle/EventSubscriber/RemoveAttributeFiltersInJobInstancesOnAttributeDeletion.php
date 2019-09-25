<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Component\Catalog\Model\AttributeInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class RemoveAttributeFiltersInJobInstancesOnAttributeDeletion implements EventSubscriberInterface
{
    /** @var ObjectRepository */
    private $jobInstanceRepository;

    /** @var BulkSaverInterface */
    private $bulkSaver;

    public function __construct(ObjectRepository $jobInstanceRepository, BulkSaverInterface $bulkSaver)
    {
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->bulkSaver = $bulkSaver;
    }

    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::POST_REMOVE => 'removeDeletedAttributeFromJobInstancesFilters',
        ];
    }

    public function removeDeletedAttributeFromJobInstancesFilters(GenericEvent $event): void
    {
        $subject = $event->getSubject();
        if (! $subject instanceof AttributeInterface) {
            return;
        }

        $jobsToUpdate = [];
        foreach ($this->jobInstanceRepository->findBy(['type' => JobInstance::TYPE_EXPORT]) as $jobInstance) {
            $rawParameters = $jobInstance->getRawParameters();
            if (isset($rawParameters['filters']['structure']['attributes'])) {
                $jobAttributeFilters = $rawParameters['filters']['structure']['attributes'];
                $newAttributeFilter = array_diff($jobAttributeFilters, [$subject->getCode()]);
                $rawParameters['filters']['structure']['attributes'] = $newAttributeFilter;
                $jobInstance->setRawParameters($rawParameters);

                $jobsToUpdate[] = $jobInstance;
            }
        }

        if (!empty($jobsToUpdate)) {
            $this->bulkSaver->saveAll($jobsToUpdate);
        }
    }
}
