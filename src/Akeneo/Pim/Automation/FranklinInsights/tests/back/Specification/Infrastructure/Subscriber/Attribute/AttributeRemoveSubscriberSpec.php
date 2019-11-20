<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\Attribute;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionIsActiveHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionIsActiveQuery;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveIdentifiersMappingCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveIdentifiersMappingHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Service\RemoveAttributesFromMappingInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Query\SelectFamilyCodesByAttributeQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\Attribute\AttributeRemoveSubscriber;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributeRemoveSubscriberSpec extends ObjectBehavior
{
    public function let(
        SelectFamilyCodesByAttributeQueryInterface $familyCodesByAttributeQuery,
        RemoveAttributesFromMappingInterface $removeAttributesFromMapping,
        GetConnectionIsActiveHandler $connectionIsActiveHandler,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        SaveIdentifiersMappingHandler $saveIdentifiersMappingHandler
    ): void {
        $connectionIsActiveHandler->handle(new GetConnectionIsActiveQuery())->willReturn(true);

        $this->beConstructedWith(
            $familyCodesByAttributeQuery,
            $removeAttributesFromMapping,
            $connectionIsActiveHandler,
            $identifiersMappingRepository,
            $saveIdentifiersMappingHandler
        );
    }

    public function it_is_a_product_family_removal_subscriber(): void
    {
        $this->shouldHaveType(AttributeRemoveSubscriber::class);
    }

    public function it_is_an_event_subscriber(): void
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_subscribes_pre_save_event(): void
    {
        $this->getSubscribedEvents()->shouldHaveKey(StorageEvents::PRE_REMOVE);
        $this->getSubscribedEvents()->shouldHaveKey(StorageEvents::POST_REMOVE);
    }

    public function it_is_only_applied_when_franklin_insights_is_activated(
        GenericEvent $event,
        AttributeInterface $attribute,
        $connectionIsActiveHandler,
        $familyCodesByAttributeQuery
    ): void {
        $event->getSubject()->willReturn($attribute);

        $connectionIsActiveHandler->handle(new GetConnectionIsActiveQuery())->willReturn(false);

        $familyCodesByAttributeQuery->execute(Argument::any())->shouldNotBeCalled();
        $this->onPreRemove($event);
    }

    public function it_is_only_applied_when_an_attribute_is_removed(
        GenericEvent $event,
        FamilyInterface $family,
        $familyCodesByAttributeQuery
    ): void {
        $event->getSubject()->willReturn($family);

        $familyCodesByAttributeQuery->execute(Argument::any())->shouldNotBeCalled();

        $this->onPreRemove($event);
    }

    public function it_gets_family_codes_and_update_identifiers_mapping_on_pre_remove(
        GenericEvent $event,
        AttributeInterface $asin,
        AttributeInterface $upc,
        $familyCodesByAttributeQuery,
        $identifiersMappingRepository,
        $saveIdentifiersMappingHandler
    ): void {
        $event->getSubject()->willReturn($upc);
        $upc->getCode()->willReturn('attribute_code');
        $asin->getCode()->willReturn('asin');

        $familyCodesByAttributeQuery->execute('attribute_code')->shouldBeCalled();

        $identifiersMappingRepository->find()->willReturn(new IdentifiersMapping([
            'asin' => 'asin',
            'upc' => 'attribute_code',
            'brand' => null,
            'mpn' => null,
        ]));

        $saveIdentifiersMappingHandler->handle(new SaveIdentifiersMappingCommand([
            'asin' => 'asin',
            'upc' => null,
            'brand' => null,
            'mpn' => null,
        ]))->shouldBeCalled();

        $this->onPreRemove($event);
    }

    public function it_publishes_a_new_job_in_the_job_queue(
        GenericEvent $preRemoveEvent,
        GenericEvent $postRemoveEvent,
        AttributeInterface $attribute,
        $familyCodesByAttributeQuery,
        $removeAttributesFromMapping,
        $identifiersMappingRepository
    ): void {
        $preRemoveEvent->getSubject()->willReturn($attribute);
        $attribute->getCode()->willReturn('attribute_code');

        $familyCodesByAttributeQuery->execute('attribute_code')->willReturn(['family_1', 'family_2']);

        $identifiersMappingRepository->find()->willReturn(new IdentifiersMapping([]));

        $this->onPreRemove($preRemoveEvent);

        $postRemoveEvent->getSubject()->willReturn($attribute);
        $attribute->getCode()->willReturn('attribute_code');

        $removeAttributesFromMapping
            ->process(['family_1', 'family_2'], ['attribute_code'])
            ->shouldBeCalled();

        $this->onPostRemove($postRemoveEvent);
    }

    public function it_does_not_update_identifiers_mapping_if_removed_attribute_is_not_an_identifier(
        GenericEvent $event,
        AttributeInterface $upc,
        $familyCodesByAttributeQuery,
        $identifiersMappingRepository,
        $saveIdentifiersMappingHandler
    ): void {
        $event->getSubject()->willReturn($upc);
        $upc->getCode()->willReturn('attribute_code');

        $familyCodesByAttributeQuery->execute('attribute_code')->shouldBeCalled();

        $identifiersMappingRepository->find()->willReturn(new IdentifiersMapping([]));
        $saveIdentifiersMappingHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->onPreRemove($event);
    }
}
