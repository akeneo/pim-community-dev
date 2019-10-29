<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveFamilyFromJobInstanceFiltersOnFamilyDeletionSubscriber implements EventSubscriberInterface
{
    /** @var ObjectRepository */
    private $jobInstanceRepository;

    /** @var BulkSaverInterface */
    private $bulkSaver;

    public function __construct(EntityManagerInterface $em, BulkSaverInterface $bulkSaver)
    {
        $this->jobInstanceRepository = $em->getRepository(JobInstance::class);
        $this->bulkSaver = $bulkSaver;
    }
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::POST_REMOVE => 'removeDeletedFamilyFromExportJobInstancesFilters',
        ];
    }

    public function removeDeletedFamilyFromExportJobInstancesFilters(GenericEvent $event): void
    {
        $family = $event->getSubject();
        if (!$family instanceof FamilyInterface) {
            return;
        }

        $familyCode = $family->getCode();
        $jobInstances = $this->jobInstanceRepository->findBy(['type' => JobInstance::TYPE_EXPORT]);
        $jobsToUpdate = [];

        /** @var JobInstance $jobInstance */
        foreach ($jobInstances as $jobInstance) {
            $rawParameters = $jobInstance->getRawParameters();
            foreach ($rawParameters['filters']['data'] ?? [] as $filterIndex => $filter) {
                if ($this->isFamilyInFilter($familyCode, $filter)) {
                    $updatedRawParameters = $this->removeFamilyCodeFromRawParameters($familyCode, $rawParameters, $filterIndex);
                    $jobInstance->setRawParameters($updatedRawParameters);
                    $jobsToUpdate[] = $jobInstance;
                    break;
                }
            }
        }

        if (!empty($jobsToUpdate)) {
            $this->bulkSaver->saveAll($jobsToUpdate);
        }
    }

    private function isFamilyInFilter(string $familyCode, array $filter): bool
    {
        return 'family' === $filter['field']
            && is_array($filter['value'])
            && in_array($familyCode, $filter['value']);
    }

    private function removeFamilyCodeFromRawParameters(string $familyCode, array $rawParameters, int $filterIndex): array
    {
        $filter = $rawParameters['filters']['data'][$filterIndex];
        $updatedFilterValue = array_diff($filter['value'], [$familyCode]);

        if (empty($updatedFilterValue)) {
            unset($rawParameters['filters']['data'][$filterIndex]);
            $rawParameters['filters']['data'] = array_values($rawParameters['filters']['data']);
        } else {
            $rawParameters['filters']['data'][$filterIndex]['value'] = array_values($updatedFilterValue);
        }

        return $rawParameters;
    }
}
