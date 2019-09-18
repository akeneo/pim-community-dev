<?php

declare(strict_types=1);

namespace Pim\Bundle\EnrichBundle\EventListener\Storage;

use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\ORM\EntityRepository;
use Pim\Component\Catalog\Model\CategoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveCategoryFilterInJobInstanceSubscriber implements EventSubscriberInterface
{
    private const DEFAULT_CATEGORY_TREE_FILTER = ['master'];

    /** @var EntityRepository */
    private $repository;

    /** @var BulkSaverInterface */
    private $bulkSaver;

    private $computedCodes = [];

    public function __construct(EntityRepository $repository, BulkSaverInterface $bulkSaver)
    {
        $this->repository = $repository;
        $this->bulkSaver = $bulkSaver;
    }

    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_REMOVE      => 'computeAndHoldCategoryTreeCodes',
            StorageEvents::PRE_REMOVE_ALL  => 'computeAndHoldCategoryTreeCodes',
            StorageEvents::POST_REMOVE     => 'removeCategoryFilter',
            StorageEvents::POST_REMOVE_ALL => 'removeCategoryFilters',
        ];
    }

    /**
     * We can not get the child's codes of a category after delete. So when a category is going to be deleted,
     * we get all the child's codes linked to it and keep them in $computedCodes property.
     *
     * @param GenericEvent $event
     * @return RemoveCategoryFilterInJobInstanceSubscriber
     */
    public function computeAndHoldCategoryTreeCodes(GenericEvent $event): RemoveCategoryFilterInJobInstanceSubscriber
    {
        $subject = $event->getSubject();
        if ($subject instanceof CategoryInterface) {
            $this->computedCodes[$subject->getCode()] = $this->getCodeAndChildrenCodes($subject);
        }

        if (is_array($subject) && current($subject) instanceof CategoryInterface) {
            foreach ($subject as $category) {
                $this->computedCodes[$category->getCode()] = $this->getCodeAndChildrenCodes($category);
            }
        }

        return $this;
    }

    public function removeCategoryFilter(GenericEvent $event): int
    {
        $subject = $event->getSubject();
        if (!$subject instanceof CategoryInterface) {
            return 0;
        }

        if (!$event->hasArgument('unitary') || false === $event->getArgument('unitary')) {
            return 0;
        }

        $code = $subject->getCode();

        return $this->removeCategoryCodesFilterInAllJobInstances($this->computedCodes[$code] ?? [$code]);
    }

    public function removeCategoryFilters(GenericEvent $event): int
    {
        $subject = $event->getSubject();
        if (!is_array($subject) || !current($subject) instanceof CategoryInterface) {
            return 0;
        }

        $codes = [];
        foreach ($subject as $category) {
            $code = $category->getCode();
            $codes = array_merge($codes, $this->computedCodes[$code] ?? [$code]);
        }

        return $this->removeCategoryCodesFilterInAllJobInstances(array_unique($codes));
    }

    /**
     * Get the code of the category and its children.
     *
     * @param CategoryInterface $category
     * @return string[]
     */
    private function getCodeAndChildrenCodes(CategoryInterface $category): array
    {
        $codes = [$category->getCode()];
        foreach ($category->getChildren() as $child) {
            $codes = array_merge($codes, $this->getCodeAndChildrenCodes($child));
        }

        return $codes;
    }

    private function removeCategoryCodesFilterInAllJobInstances(array $categoryCodes): int
    {
        $jobsToUpdate = [];
        foreach ($this->repository->findAll() as $jobInstance) {
            if ($this->removeCategoryCodesFilterInJobInstance($jobInstance, $categoryCodes)) {
                $jobsToUpdate[] = $jobInstance;
            }
        }

        if (!empty($jobsToUpdate)) {
            $this->bulkSaver->saveAll($jobsToUpdate);
        }

        return count($jobsToUpdate);
    }

    private function removeCategoryCodesFilterInJobInstance(JobInstance $jobInstance, array $categoryCodes): bool
    {
        $rawParameters = $jobInstance->getRawParameters();

        if (is_array($rawParameters['filters']['data'] ?? null)) {
            foreach ($rawParameters['filters']['data'] as $filterKey => $filter) {
                if ('categories' !== $filter['field']) {
                    continue;
                }

                $newValues = [];
                foreach ($filter['value'] as $value) {
                    if (!in_array($value, $categoryCodes)) {
                        $newValues[] = $value;
                    }
                }

                if (count($newValues) === 0) {
                    $rawParameters['filters']['data'][$filterKey]['value'] = self::DEFAULT_CATEGORY_TREE_FILTER;
                    $jobInstance->setRawParameters($rawParameters);

                    return true;
                }

                if (count($newValues) !== count(($filter['value']))) {
                    $rawParameters['filters']['data'][$filterKey]['value'] = $newValues;
                    $jobInstance->setRawParameters($rawParameters);

                    return true;
                }
            }
        }

        return false;
    }
}
