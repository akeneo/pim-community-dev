<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\Command;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Command\UpdateAttributeGroupActivationCommand;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Event\AttributeGroupActivationHasChanged;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\AttributeGroupActivation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\AttributeGroupActivationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeGroupCode;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateAttributeGroupActivationHandlerSpec extends ObjectBehavior
{
    function let(
        AttributeGroupActivationRepositoryInterface $attributeGroupActivationRepository,
        MessageBusInterface $messageBus,
        Clock $clock
    ) {
        $this->beConstructedWith($attributeGroupActivationRepository, $messageBus, $clock);
    }

    function it_saves_an_attribute_group_activation(
        AttributeGroupActivationRepositoryInterface $attributeGroupActivationRepository,
        MessageBusInterface $messageBus,
        Clock $clock
    ) {
        $command = new UpdateAttributeGroupActivationCommand('code', true);
        $attributeGroupCode = new AttributeGroupCode('code');
        $attributeGroupActivationRepository->getForAttributeGroupCode($attributeGroupCode)
            ->shouldBeCalledOnce()->willReturn(null);

        $attributeGroupActivationRepository->save(new AttributeGroupActivation($attributeGroupCode, true))->shouldBeCalledOnce();
        $date = new \DateTimeImmutable();
        $clock->getCurrentTime()->willReturn($date);
        $messageBus->dispatch(new AttributeGroupActivationHasChanged('code', true, $date))
            ->shouldBeCalledOnce()->willReturn(new Envelope(new \stdClass()));

        $this->__invoke($command);
    }

    function it_does_not_save_an_attribute_group_activation_if_the_attribute_group_is_already_activated(
        AttributeGroupActivationRepositoryInterface $attributeGroupActivationRepository,
        MessageBusInterface $messageBus
    ) {
        $command = new UpdateAttributeGroupActivationCommand('code', true);
        $attributeGroupCode = new AttributeGroupCode('code');
        $attributeGroupActivationRepository->getForAttributeGroupCode($attributeGroupCode)
            ->shouldBeCalledOnce()->willReturn(new AttributeGroupActivation($attributeGroupCode, true));

        $attributeGroupActivationRepository->save(Argument::any())->shouldNotBeCalled();
        $messageBus->dispatch(Argument::any())->shouldNotBeCalled();

        $this->__invoke($command);
    }

    function it_saves_an_attribute_group_deactivation(
        AttributeGroupActivationRepositoryInterface $attributeGroupActivationRepository,
        MessageBusInterface $messageBus,
        Clock $clock
    ) {
        $command = new UpdateAttributeGroupActivationCommand('code', false);
        $attributeGroupCode = new AttributeGroupCode('code');
        $attributeGroupActivationRepository->getForAttributeGroupCode($attributeGroupCode)
            ->shouldBeCalledOnce()->willReturn(new AttributeGroupActivation($attributeGroupCode, true));

        $attributeGroupActivationRepository->save(new AttributeGroupActivation($attributeGroupCode, false))->shouldBeCalledOnce();
        $date = new \DateTimeImmutable();
        $clock->getCurrentTime()->willReturn($date);
        $messageBus->dispatch(new AttributeGroupActivationHasChanged('code', false, $date))
            ->shouldBeCalledOnce()->willReturn(new Envelope(new \stdClass()));

        $this->__invoke($command);
    }

    function it_does_not_save_an_attribute_group_deactivation_if_the_attribute_group_is_already_deactivated(
        AttributeGroupActivationRepositoryInterface $attributeGroupActivationRepository,
        MessageBusInterface $messageBus
    ) {
        $command = new UpdateAttributeGroupActivationCommand('code', false);
        $attributeGroupCode = new AttributeGroupCode('code');
        $attributeGroupActivationRepository->getForAttributeGroupCode($attributeGroupCode)
            ->shouldBeCalledOnce()->willReturn(new AttributeGroupActivation($attributeGroupCode, false));

        $attributeGroupActivationRepository->save(Argument::any())->shouldNotBeCalled();
        $messageBus->dispatch(Argument::any())->shouldNotBeCalled();

        $this->__invoke($command);
    }

    function it_does_not_save_an_attribute_group_deactivation_if_the_attribute_group_is_not_present_in_database(
        AttributeGroupActivationRepositoryInterface $attributeGroupActivationRepository,
        MessageBusInterface $messageBus
    ) {
        $command = new UpdateAttributeGroupActivationCommand('code', false);
        $attributeGroupCode = new AttributeGroupCode('code');
        $attributeGroupActivationRepository->getForAttributeGroupCode($attributeGroupCode)
            ->shouldBeCalledOnce()->willReturn(null);

        $attributeGroupActivationRepository->save(Argument::any())->shouldNotBeCalled();
        $messageBus->dispatch(Argument::any())->shouldNotBeCalled();

        $this->__invoke($command);
    }
}
