<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Job;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\Notification;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;

class RemoveCompletenessForChannelAndLocaleTaskletSpec extends ObjectBehavior
{
    public function it_executes_the_job_cleans_products_and_channel(
        StepExecution $stepExecution,
        NotifierInterface $notifier,
        SimpleFactoryInterface $notificationFactory,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $productsCursor,
        EntityManagerClearerInterface $cacheClearer,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        ChannelInterface $ecommerce,
        LocaleInterface $enUs,
        LocaleInterface $frFR,
        BulkSaverInterface $localeBulkSaver
    ): void {
        $this->beConstructedWith(
            $cacheClearer,
            $notifier,
            $notificationFactory,
            $productQueryBuilderFactory,
            $channelRepository,
            $localeRepository,
            $localeBulkSaver,
            'rootDir',
            'prod',
            2
        );
        $this->setStepExecution($stepExecution);

        $jeanProduct = (new Product())->setIdentifier('jean');
        $shoeProduct = (new Product())->setIdentifier('shoe');
        $hatProduct = (new Product())->setIdentifier('hat');
        $jobParameters = new JobParameters([
            'locales_identifier' => ['en_US', 'fr_FR'],
            'channel_code' => 'ecommerce',
            'username' => 'willypapa'
        ]);
        $stepExecution->getJobParameters()->willReturn($jobParameters);

        $startNotification = new Notification();
        $doneNotification = new Notification();
        $notificationFactory->create()->willReturn($startNotification, $doneNotification);
        $notifier->notify($startNotification, ['willypapa'])->shouldBeCalled();
        $notifier->notify($doneNotification, ['willypapa'])->shouldBeCalled();

        $productQueryBuilderFactory->create()->willReturn($pqb);
        $pqb->execute()->willReturn($productsCursor);

        $productsCursor->rewind()->shouldBeCalled();
        $productsCursor->current()->willReturn($jeanProduct, $shoeProduct, $hatProduct);
        $productsCursor->next()->shouldBeCalled();
        $productsCursor->valid()->willReturn(true, true, true, false);

        $cacheClearer->clear()->shouldBeCalled();

        $channelRepository->findOneByIdentifier('ecommerce')->willReturn($ecommerce);
        $localeRepository->findBy(['code' => ['en_US', 'fr_FR']])->willReturn([$enUs, $frFR]);
        $enUs->removeChannel($ecommerce)->shouldBeCalled();
        $frFR->removeChannel($ecommerce)->shouldBeCalled();

        $localeBulkSaver->saveAll([$enUs, $frFR])->shouldBeCalled();

        $this->execute();
    }
}
