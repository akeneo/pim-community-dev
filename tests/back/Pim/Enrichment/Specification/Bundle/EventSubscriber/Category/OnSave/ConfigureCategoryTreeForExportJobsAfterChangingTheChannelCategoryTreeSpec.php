<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Category\OnSave;

use Akeneo\Channel\Component\Event\ChannelCategoryHasBeenUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Paul Chasle <paul.chasle@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConfigureCategoryTreeForExportJobsAfterChangingTheChannelCategoryTreeSpec extends ObjectBehavior
{
    private const supportedJobNames = [
        'csv_product_export',
        'xlsx_product_export',
    ];

    public function let(
        ObjectRepository $jobInstanceRepository,
        ObjectUpdaterInterface $jobInstanceUpdater,
        BulkSaverInterface $jobInstanceSaver
    ): void {
        $this->beConstructedWith(
            $jobInstanceRepository,
            $jobInstanceUpdater,
            $jobInstanceSaver,
            self::supportedJobNames
        );
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_subscribes_to_channel_category_has_been_updated_event()
    {
        $this->getSubscribedEvents()->shouldReturn(
            [ChannelCategoryHasBeenUpdated::class => 'onChannelCategoryHasBeenUpdatedEvent']
        );
    }

    function it_updates_export_profiles_when_the_category_of_their_channels_is_updated(
        $jobInstanceRepository,
        $jobInstanceUpdater,
        $jobInstanceSaver,
        JobInstance $jobInstance
    ) {
        $jobInstance->getRawParameters()->willReturn(
            [
                'filters' => [
                    'structure' => ['scope' => 'channel_code'],
                    'data' => [
                        [
                            'field' => 'categories',
                            'operator' => Operators::IN_CHILDREN_LIST,
                            'value' => ['category_code'],
                        ],
                    ],
                ],
            ]
        );

        $jobInstanceRepository->findBy(['jobName' => self::supportedJobNames])->shouldBeCalled()->willReturn(
            [$jobInstance]
        );

        $jobInstanceUpdater->update(
            $jobInstance,
            [
                'configuration' => [
                    'filters' => [
                        'structure' => ['scope' => 'channel_code'],
                        'data' => [
                            [
                                'field' => 'categories',
                                'operator' => Operators::IN_CHILDREN_LIST,
                                'value' => ['other_category_code'],
                            ],
                        ],
                    ],
                ],
            ]
        )->shouldBeCalled();

        $jobInstanceSaver->saveAll([$jobInstance])->shouldBeCalled();

        $event = new ChannelCategoryHasBeenUpdated('channel_code', 'previous-category-code', 'other_category_code');
        $this->onChannelCategoryHasBeenUpdatedEvent($event);
    }
}
