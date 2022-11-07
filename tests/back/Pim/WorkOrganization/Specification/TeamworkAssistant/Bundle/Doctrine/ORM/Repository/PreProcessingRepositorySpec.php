<?php

namespace Specification\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Doctrine\ORM\Repository;

use Akeneo\Channel\Infrastructure\Component\Model\Channel;
use Akeneo\Channel\Infrastructure\Component\Model\Locale;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Doctrine\ORM\Repository\PreProcessingRepository;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Doctrine\ORM\TableNameMapper;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\PreProcessingRepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;

class PreProcessingRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $entityManager, Connection $connection)
    {
        $this->beConstructedWith($entityManager);

        $entityManager->getConnection()->willReturn($connection);
    }

    function it_is_pre_processing_repository()
    {
        $this->shouldImplement(PreProcessingRepositoryInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PreProcessingRepository::class);
    }

    function it_adds_products_to_a_project(
        $entityManager,
        Connection $connection,
        ProjectInterface $project,
        ProductInterface $product
    ) {
        $productUuid = Uuid::fromString('f7901bb1-3bfb-4f00-83a5-72fe8edf8da3');
        $project->getId()->willReturn(13);
        $product->getUuid()->willReturn($productUuid);

        $entityManager->getConnection()->willReturn($connection);

        $connection->insert('pimee_teamwork_assistant_project_product', [
            'project_id' => 13,
            'product_uuid' => $productUuid->getBytes(),
        ])->shouldBeCalled();

        $this->addProduct($project, $product);
    }

    function it_prepares_the_project_calculation_by_deleting_associated_products(
        $connection,
        ProjectInterface $project
    ) {
        $project->getId()->willReturn(40);

        $connection->delete('pimee_teamwork_assistant_project_product', [
            'project_id' => 40,
        ])->shouldBeCalled();

        $this->prepareProjectCalculation($project)->shouldReturn(null);
    }

    function it_is_processable_attribute_group_completeness(
        $entityManager,
        ProductInterface $product,
        ProjectInterface $project,
        FamilyInterface $family,
        Connection $connection,
        Channel $channel,
        Locale $locale
    ) {
        $productUuid = Uuid::fromString('f7901bb1-3bfb-4f00-83a5-72fe8edf8da3');
        $productUpdated = new \Datetime('2019-08-01 10:00:00');
        $familyUpdated = new \Datetime('2019-08-26 13:46:35');
        $projectChannelId = 'ecommerce';
        $projectLocaleId = 'en_US';
        $calculatedAt = '2019-08-01 10:00:10';

        $product->getUuid()->willReturn($productUuid);
        $product->getFamily()->willReturn($family);
        $product->getUpdated()->willReturn($productUpdated);

        $project->getChannel()->willReturn($channel);
        $project->getLocale()->willReturn($locale);

        $channel->getId()->willReturn($projectChannelId);
        $locale->getId()->willReturn($projectLocaleId);
        $family->getUpdated()->willReturn($familyUpdated);

        $entityManager->getConnection()->willReturn($connection);

        $connection
            ->fetchOne(Argument::type('string'), [
                'product_uuid' => $productUuid->getBytes(),
                'channel_id' => $projectChannelId,
                'locale_id'  => $projectLocaleId,
            ])
            ->willReturn($calculatedAt);

        $product->getUpdated()->shouldBeCalled();
        $family->getUpdated()->shouldBeCalled();

        $this->isProcessableAttributeGroupCompleteness($product, $project)->shouldReturn(true);
    }

    function it_is_processable_attribute_group_completeness_when_product_has_not_family(
        $entityManager,
        ProductInterface $product,
        ProjectInterface $project,
        Connection $connection,
        Channel $channel,
        Locale $locale
    ) {
        $productUuid = Uuid::fromString('f7901bb1-3bfb-4f00-83a5-72fe8edf8da3');
        $productUpdated = new \Datetime('2019-08-01 11:00:00');
        $projectChannelId = 'ecommerce';
        $projectLocaleId = 'en_US';
        $calculatedAt = '2019-08-01 10:00:00';

        $product->getUuid()->willReturn($productUuid);
        $product->getFamily()->willReturn(null);
        $product->getUpdated()->willReturn($productUpdated);

        $project->getChannel()->willReturn($channel);
        $project->getLocale()->willReturn($locale);

        $channel->getId()->willReturn($projectChannelId);
        $locale->getId()->willReturn($projectLocaleId);

        $entityManager->getConnection()->willReturn($connection);

        $connection
            ->fetchOne(Argument::type('string'), [
                'product_uuid' => $productUuid->getBytes(),
                'channel_id' => $projectChannelId,
                'locale_id'  => $projectLocaleId,
            ])
            ->willReturn($calculatedAt);

        $product->getUpdated()->shouldBeCalled();

        $this->isProcessableAttributeGroupCompleteness($product, $project)->shouldReturn(true);
    }

    function it_is_processable_attribute_group_completeness_when_project_has_not_been_calculated_yet(
        $entityManager,
        ProductInterface $product,
        ProjectInterface $project,
        Connection $connection,
        Channel $channel,
        Locale $locale
    ) {
        $productUuid = Uuid::fromString('f7901bb1-3bfb-4f00-83a5-72fe8edf8da3');
        $productUpdated = new \Datetime('2019-08-01 11:00:00');
        $projectChannelId = 'ecommerce';
        $projectLocaleId = 'en_US';
        $calculatedAt = null;

        $product->getUuid()->willReturn($productUuid);
        $product->getFamily()->willReturn(null);
        $product->getUpdated()->willReturn($productUpdated);

        $project->getChannel()->willReturn($channel);
        $project->getLocale()->willReturn($locale);

        $channel->getId()->willReturn($projectChannelId);
        $locale->getId()->willReturn($projectLocaleId);

        $entityManager->getConnection()->willReturn($connection);

        $connection
            ->fetchOne(Argument::type('string'), [
                'product_uuid' => $productUuid->getBytes(),
                'channel_id' => $projectChannelId,
                'locale_id'  => $projectLocaleId,
            ])
            ->willReturn($calculatedAt);

        $product->getUpdated()->shouldNotBeCalled();

        $this->isProcessableAttributeGroupCompleteness($product, $project)->shouldReturn(true);
    }
}
