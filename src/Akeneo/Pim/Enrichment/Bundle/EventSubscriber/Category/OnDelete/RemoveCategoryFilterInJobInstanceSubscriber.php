<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Category\OnDelete;

use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveCategoryFilterInJobInstanceSubscriber implements EventSubscriberInterface
{
    private const DEFAULT_CATEGORY_FILTER_VALUE    = ['master'];
    private const DEFAULT_CATEGORY_FILTER_OPERATOR = 'IN CHILDREN';

    /** @var EntityRepository */
    private $repository;

    /** @var BulkSaverInterface */
    private $bulkSaver;

    /** @var array */
    private $computedCodes = [];

    /** @var array */
    private $computedRootCodes = [];

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
     */
    public function computeAndHoldCategoryTreeCodes(GenericEvent $event): void
    {
        $subject = $event->getSubject();
        if ($subject instanceof CategoryInterface) {
            if (!$event->hasArgument('unitary') || false === $event->getArgument('unitary')) {
                return;
            }

            $this->computedCodes[$subject->getCode()] = $this->getCodeAndChildrenCodes($subject);
            $this->computedRootCodes[] = $this->getRootCategoryCode($subject);

            return;
        }

        if (is_array($subject) && current($subject) instanceof CategoryInterface) {
            foreach ($subject as $category) {
                $this->computedCodes[$category->getCode()] = $this->getCodeAndChildrenCodes($category);
                $this->computedRootCodes[] = $this->getRootCategoryCode($category);
            }
        }
    }

    /**
     * Returns the number of updated jobs.
     *
     * @param GenericEvent $event
     * @return int
     */
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

    /**
     * Returns the number of updated jobs.
     *
     * @param GenericEvent $event
     * @return int
     */
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

    private function getRootCategoryCode(CategoryInterface $category): string
    {
        $parentCategory = $category->getParent();
        if (null === $parentCategory) {
            return $category->getCode();
        }

        return $this->getRootCategoryCode($parentCategory);
    }

    private function removeCategoryCodesFilterInAllJobInstances(array $categoryCodes): int
    {
        $jobsToUpdate = [];
        foreach ($this->repository->findAll() as $jobInstance) {
            if ($this->updateCategoryCodesFilterInJobInstance($jobInstance, $categoryCodes)) {
                $jobsToUpdate[] = $jobInstance;
            }
        }

        if (!empty($jobsToUpdate)) {
            $this->bulkSaver->saveAll($jobsToUpdate);
        }

        return count($jobsToUpdate);
    }

    /**
     * Returns true if $jobInstance object is updated, false otherwise.
     *
     * @param JobInstance $jobInstance
     * @param array       $categoryCodes
     * @return bool
     */
    private function updateCategoryCodesFilterInJobInstance(JobInstance $jobInstance, array $categoryCodes): bool
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

                if (empty($newValues)) {
                    $rawParameters['filters']['data'][$filterKey]['value'] = empty($this->computedRootCodes)
                        ? self::DEFAULT_CATEGORY_FILTER_VALUE
                        : array_unique($this->computedRootCodes)
                    ;
                    $rawParameters['filters']['data'][$filterKey]['operator'] = self::DEFAULT_CATEGORY_FILTER_OPERATOR;
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
