<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Job;

use Akeneo\Channel\Component\Model\Channel;
use Akeneo\Channel\Component\Model\Locale;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\Notification;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RemoveCompletenessForChannelAndLocaleTaskletSpec extends ObjectBehavior
{
    public function let(
        EntityManagerClearerInterface $cacheClearer,
        NotifierInterface $notifier,
        SimpleFactoryInterface $notificationFactory,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        CursorableRepositoryInterface $productRepository,
        ChannelRepositoryInterface $channelRepository,
        BulkSaverInterface $productBulkSaver,
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $enUS = new Locale();
        $enUS->setCode('en_US');
        $frFr = new Locale();
        $frFr->setCode('fr_FR');
        $esEs = new Locale();
        $esEs->setCode('es_ES');
        $channel = new Channel();
        $channel->addLocale($enUS);
        $channel->addLocale($frFr);
        $channel->addLocale($esEs);

        $channelRepository->findOneByIdentifier('ecommerce')->willReturn($channel);
        $channelRepository->findOneByIdentifier(Argument::not('ecommerce'))->willReturn(null);

        $this->beConstructedWith(
            $cacheClearer,
            $notifier,
            $notificationFactory,
            $productQueryBuilderFactory,
            $productRepository,
            $channelRepository,
            $productBulkSaver,
            2
        );
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $this->setStepExecution($stepExecution);
    }

    function it_does_nothing_if_locales_are_still_bound_to_channel(
        NotifierInterface $notifier,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        JobParameters $jobParameters
    ) {
        $jobParameters->get('channel_code')->willReturn('ecommerce');
        $jobParameters->get('locales_identifier')->willReturn(['en_US', 'fr_FR']);
        $productQueryBuilderFactory->create()->shouldNotBeCalled();
        $notifier->notify(Argument::cetera())->shouldNotBeCalled();

        $this->execute();
    }

    function it_executes_the_job_if_channel_does_not_exist_anymore(
        NotifierInterface $notifier,
        SimpleFactoryInterface $notificationFactory,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        CursorableRepositoryInterface $productRepository,
        BulkSaverInterface $productBulkSaver,
        JobParameters $jobParameters,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $productsCursor,
    ) {
        $jobParameters->get('channel_code')->willReturn('unknown');
        $jobParameters->get('locales_identifier')->willReturn(['de_DE', 'it_IT']);
        $jobParameters->get('username')->willReturn('willypapa');

        $jeanProduct = (new Product())->setIdentifier('jean');

        $startNotification = new Notification();
        $doneNotification = new Notification();
        $notificationFactory->create()->willReturn($startNotification, $doneNotification);
        $notifier->notify($startNotification, ['willypapa'])->shouldBeCalled();
        $notifier->notify($doneNotification, ['willypapa'])->shouldBeCalled();

        $productQueryBuilderFactory->create()->willReturn($pqb);
        $pqb->execute()->willReturn($productsCursor);

        $productsCursor->rewind()->shouldBeCalled();
        $productsCursor->current()->willReturn(
            new IdentifierResult('jean', ProductInterface::class),
        );
        $productsCursor->next()->shouldBeCalled();
        $productsCursor->valid()->willReturn(true, false);

        $productRepository->getItemsFromIdentifiers(['jean'])->willReturn([$jeanProduct]);

        $productBulkSaver->saveAll([$jeanProduct], ['force_save' => true])->shouldBeCalledOnce();

        $this->execute();
    }

    function it_executes_the_job_cleans_products_and_channel(
        EntityManagerClearerInterface $cacheClearer,
        NotifierInterface $notifier,
        SimpleFactoryInterface $notificationFactory,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        CursorableRepositoryInterface $productRepository,
        BulkSaverInterface $productBulkSaver,
        JobParameters $jobParameters,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $productsCursor
    ): void {
        $jeanProduct = (new Product())->setIdentifier('jean');
        $shoeProduct = (new Product())->setIdentifier('shoe');
        $hatProduct = (new Product())->setIdentifier('hat');

        $jobParameters->get('channel_code')->willReturn('ecommerce');
        $jobParameters->get('locales_identifier')->willReturn(['de_DE', 'it_IT']);
        $jobParameters->get('username')->willReturn('willypapa');

        $startNotification = new Notification();
        $doneNotification = new Notification();
        $notificationFactory->create()->willReturn($startNotification, $doneNotification);
        $notifier->notify($startNotification, ['willypapa'])->shouldBeCalled();
        $notifier->notify($doneNotification, ['willypapa'])->shouldBeCalled();

        $productQueryBuilderFactory->create()->willReturn($pqb);
        $pqb->execute()->willReturn($productsCursor);

        $productsCursor->rewind()->shouldBeCalled();
        $productsCursor->current()->willReturn(
            new IdentifierResult('jean', ProductInterface::class),
            new IdentifierResult('shoe', ProductInterface::class),
            new IdentifierResult('hat', ProductInterface::class)
        );
        $productsCursor->next()->shouldBeCalled();
        $productsCursor->valid()->willReturn(true, true, true, false);

        $productRepository->getItemsFromIdentifiers(['jean', 'shoe'])->willReturn([$jeanProduct, $shoeProduct]);
        $productRepository->getItemsFromIdentifiers(['hat'])->willReturn([$hatProduct]);
        $cacheClearer->clear()->shouldBeCalled();

        $productBulkSaver->saveAll([$jeanProduct, $shoeProduct], ['force_save' => true])->shouldBeCalledOnce();
        $productBulkSaver->saveAll([$hatProduct], ['force_save' => true])->shouldBeCalledOnce();

        $this->execute();
    }
}
