<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author    Julian Prud'Homme <julian.prudhomme@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
                $newAttributeFilter = array_values(array_diff($jobAttributeFilters, [$subject->getCode()]));

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
