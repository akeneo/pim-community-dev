<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Category\OnDelete;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Category\OnDelete\RemoveCategoryFilterInJobInstanceSubscriber;
use Akeneo\Pim\Enrichment\Component\Category\Model\Category;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\ORM\EntityRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveCategoryFilterInJobInstanceSubscriberSpec extends ObjectBehavior
{
    function let(EntityRepository $repository, BulkSaverInterface $bulkSaver)
    {
        $this->beConstructedWith($repository, $bulkSaver);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RemoveCategoryFilterInJobInstanceSubscriber::class);
    }

   function it_implements_event_subscriber_interface()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }


    function it_subscribes_to_storage_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            StorageEvents::PRE_REMOVE      => 'computeAndHoldCategoryTreeCodes',
            StorageEvents::PRE_REMOVE_ALL  => 'computeAndHoldCategoryTreeCodes',
            StorageEvents::POST_REMOVE     => 'removeCategoryFilter',
            StorageEvents::POST_REMOVE_ALL => 'removeCategoryFilters',
        ]);
    }

    function it_removes_category_in_job_filter(
        EntityRepository $repository,
        BulkSaverInterface $bulkSaver,
        JobInstance $jobInstance1,
        JobInstance $jobInstance2
    ) {
        $category = $this->createCategory('code');
        $event = new GenericEvent($category, ['unitary' => true]);

        $repository->findAll()->willReturn([$jobInstance1, $jobInstance2]);
        $jobInstance1->getRawParameters()->willReturn([
            'filters' => [
                'data' => [
                    [
                        'field' => 'categories',
                        'operator' => 'IN',
                        'value' => ['value1', 'value2'],
                    ],
                ],
            ],
        ]);
        $jobInstance2->getRawParameters()->willReturn([
            'filters' => [
                'data' => [
                    [
                        'field' => 'categories',
                        'operator' => 'IN',
                        'value' => ['value1', 'code'],
                    ],
                ],
            ],
        ]);

        $jobInstance2->setRawParameters([
            'filters' => [
                'data' => [
                    [
                        'field' => 'categories',
                        'operator' => 'IN',
                        'value' => ['value1'],
                    ],
                ],
            ],
        ])->shouldBeCalled();
        $bulkSaver->saveAll([$jobInstance2])->shouldBeCalled();

        $this->removeCategoryFilter($event)->shouldReturn(1);
    }

    function it_does_not_remove_unitary(
        EntityRepository $repository,
        BulkSaverInterface $bulkSaver,
        JobInstance $jobInstance1,
        JobInstance $jobInstance2
    ) {
        $category = $this->createCategory('code');
        $event = new GenericEvent($category, ['unitary' => false]);

        $repository->findAll()->willReturn([$jobInstance1, $jobInstance2]);
        $jobInstance1->getRawParameters()->willReturn([
            'filters' => [
                'data' => [
                    [
                        'field' => 'categories',
                        'operator' => 'IN',
                        'value' => ['value1', 'value2'],
                    ],
                ],
            ],
        ]);
        $jobInstance2->getRawParameters()->willReturn([
            'filters' => [
                'data' => [
                    [
                        'field' => 'categories',
                        'operator' => 'IN',
                        'value' => ['value1', 'code'],
                    ],
                ],
            ],
        ]);

        $jobInstance2->setRawParameters(Argument::cetera())->shouldNotBeCalled();
        $bulkSaver->saveAll([$jobInstance2])->shouldNotBeCalled();

        $this->removeCategoryFilter($event)->shouldReturn(0);
    }

    function it_removes_child_category_in_job_filter(
        EntityRepository $repository,
        BulkSaverInterface $bulkSaver,
        JobInstance $jobInstance1,
        JobInstance $jobInstance2
    ) {
        $category = $this->createCategory('code');
        $parentCategory = $this->createCategory('parent_code', [$category]);

        $event = new GenericEvent($parentCategory, ['unitary' => true]);

        $repository->findAll()->willReturn([$jobInstance1, $jobInstance2]);
        $jobInstance1->getRawParameters()->willReturn([
            'filters' => [
                'data' => [
                    [
                        'field' => 'categories',
                        'operator' => 'IN',
                        'value' => ['value1', 'value2'],
                    ],
                ],
            ],
        ]);
        $jobInstance2->getRawParameters()->willReturn([
            'filters' => [
                'data' => [
                    [
                        'field' => 'categories',
                        'operator' => 'IN',
                        'value' => ['value1', 'code'],
                    ],
                ],
            ],
        ]);

        $jobInstance2->setRawParameters([
            'filters' => [
                'data' => [
                    [
                        'field' => 'categories',
                        'operator' => 'IN',
                        'value' => ['value1'],
                    ],
                ],
            ],
        ])->shouldBeCalled();
        $bulkSaver->saveAll([$jobInstance2])->shouldBeCalled();

        $this->computeAndHoldCategoryTreeCodes($event);
        $this->removeCategoryFilter($event)->shouldReturn(1);
    }

    function it_removes_categories_in_job_filter(
        EntityRepository $repository,
        BulkSaverInterface $bulkSaver,
        JobInstance $jobInstance1,
        JobInstance $jobInstance2
    ) {
        $category1 = $this->createCategory('code1');
        $category2 = $this->createCategory('code2');
        $event = new GenericEvent([$category1, $category2]);

        $repository->findAll()->willReturn([$jobInstance1, $jobInstance2]);
        $jobInstance1->getRawParameters()->willReturn([
            'filters' => [
                'data' => [
                    [
                        'field' => 'categories',
                        'operator' => 'IN',
                        'value' => ['value1', 'code1', 'code2'],
                    ],
                ],
            ],
        ]);
        $jobInstance2->getRawParameters()->willReturn([
            'filters' => [
                'data' => [
                    [
                        'field' => 'categories',
                        'operator' => 'IN',
                        'value' => ['code2'],
                    ],
                ],
            ],
        ]);

        $jobInstance1->setRawParameters([
            'filters' => [
                'data' => [
                    [
                        'field' => 'categories',
                        'operator' => 'IN',
                        'value' => ['value1'],
                    ],
                ],
            ],
        ])->shouldBeCalled();
        $jobInstance2->setRawParameters([
            'filters' => [
                'data' => [
                    [
                        'field' => 'categories',
                        'operator' => 'IN CHILDREN',
                        'value' => ['master'],
                    ],
                ],
            ],
        ])->shouldBeCalled();
        $bulkSaver->saveAll([$jobInstance1, $jobInstance2])->shouldBeCalled();

        $this->removeCategoryFilters($event)->shouldReturn(2);
    }

    function it_removes_child_categories_in_job_filter(
        EntityRepository $repository,
        BulkSaverInterface $bulkSaver,
        JobInstance $jobInstance1,
        JobInstance $jobInstance2
    ) {
        $category1 = $this->createCategory('code1');
        $parentCategory1 = $this->createCategory('parent_code1', [$category1]);

        $subCategory = $this->createCategory('sub_code');
        $category2 = $this->createCategory('code2', [$subCategory]);
        $parentCategory2 = $this->createCategory('parent_code2', [$category2]);

        $event = new GenericEvent([$parentCategory1, $parentCategory2]);

        $repository->findAll()->willReturn([$jobInstance1, $jobInstance2]);
        $jobInstance1->getRawParameters()->willReturn([
            'filters' => [
                'data' => [
                    [
                        'field' => 'categories',
                        'operator' => 'IN',
                        'value' => ['value1', 'code1', 'code2'],
                    ],
                ],
            ],
        ]);
        $jobInstance2->getRawParameters()->willReturn([
            'filters' => [
                'data' => [
                    [
                        'field' => 'categories',
                        'operator' => 'IN',
                        'value' => ['sub_code'],
                    ],
                ],
            ],
        ]);

        $jobInstance1->setRawParameters([
            'filters' => [
                'data' => [
                    [
                        'field' => 'categories',
                        'operator' => 'IN',
                        'value' => ['value1'],
                    ],
                ],
            ],
        ])->shouldBeCalled();
        $jobInstance2->setRawParameters([
            'filters' => [
                'data' => [
                    [
                        'field' => 'categories',
                        'operator' => 'IN CHILDREN',
                        'value' => ['parent_code1', 'parent_code2'],
                    ],
                ],
            ],
        ])->shouldBeCalled();
        $bulkSaver->saveAll([$jobInstance1, $jobInstance2])->shouldBeCalled();

        $this->computeAndHoldCategoryTreeCodes($event);
        $this->removeCategoryFilters($event)->shouldReturn(2);
    }

    private function createCategory(string $code, array $children = []): Category
    {
        $category = new Category();
        $category->setCode($code);
        foreach ($children as $child) {
            $category->addChild($child);
        }

        return $category;
    }
}
