<?php

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Subscriber;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\GenerateIdentifierCommandHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\Property\GenerateAutoNumberHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\Property\GenerateFreeTextHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Delimiter;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorCode;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorId;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\LabelCollection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Structure;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Subscriber\SetIdentifiersSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SetIdentifiersSubscriberSpec extends ObjectBehavior
{
    public function let(
        IdentifierGeneratorRepository $identifierGeneratorRepository,
        ValidatorInterface $validator,
    ) {
        $this->beConstructedWith(
            $identifierGeneratorRepository,
            new GenerateIdentifierCommandHandler(
                new GenerateAutoNumberHandler(),
                new GenerateFreeTextHandler(),
            ),
            $validator
        );
    }

    public function it_should_be_an_event_subscriber()
    {
        $this->beAnInstanceOf(SetIdentifiersSubscriber::class);
        $this->beAnInstanceOf(EventSubscriberInterface::class);
    }

    public function it_should_listen_to_pre_save_events()
    {
        Assert::assertSame(\array_keys($this->getSubscribedEvents()->getWrappedObject()), [
            StorageEvents::PRE_SAVE,
            StorageEvents::PRE_SAVE_ALL
        ]);
    }

    public function it_should_generate_an_identifier(
        IdentifierGeneratorRepository $identifierGeneratorRepository,
        ValidatorInterface $validator,
        ProductInterface $product,
    ) {
        $product->getIdentifier()->shouldBeCalled()->willReturn(null);
        $identifierGeneratorRepository->getAll()->shouldBeCalled()->willReturn([$this->getIdentifierGenerator()]);
        $value = ScalarValue::value('sku', 'AKN');
        $product->addValue($value)->shouldBeCalled();
        $validator->validate($product)->shouldBeCalled()->willReturn(new ConstraintViolationList());

        $this->setIdentifier(new GenericEvent($product->getWrappedObject()));
    }

    public function it_should_rollback_when_identifier_is_invalid(
        IdentifierGeneratorRepository $identifierGeneratorRepository,
        ValidatorInterface $validator,
        ProductInterface $product,
        ConstraintViolationInterface $constraintViolation,
    ) {
        $product->getIdentifier()->shouldBeCalled()->willReturn(null);
        $identifierGeneratorRepository->getAll()->shouldBeCalled()->willReturn([$this->getIdentifierGenerator()]);
        $value = ScalarValue::value('sku', 'AKN');
        $product->addValue($value)->shouldBeCalled();
        $validator->validate($product)->shouldBeCalled()->willReturn(new ConstraintViolationList([
            $constraintViolation->getWrappedObject()
        ]));
        $product->removeValue($value)->shouldBeCalled();

        $this->setIdentifier(new GenericEvent($product->getWrappedObject()));
    }

    public function it_should_generate_several_identifiers(
        IdentifierGeneratorRepository $identifierGeneratorRepository,
        ValidatorInterface $validator,
        ProductInterface $product1,
        ProductInterface $product2,
    ) {
        $product1->getIdentifier()->shouldBeCalled()->willReturn(null);
        $product2->getIdentifier()->shouldBeCalled()->willReturn(null);
        $identifierGeneratorRepository->getAll()->shouldBeCalled()->willReturn([$this->getIdentifierGenerator()]);
        $value = ScalarValue::value('sku', 'AKN');
        $product1->addValue($value)->shouldBeCalled();
        $product2->addValue($value)->shouldBeCalled();
        $validator->validate($product1)->shouldBeCalled()->willReturn(new ConstraintViolationList());
        $validator->validate($product2)->shouldBeCalled()->willReturn(new ConstraintViolationList());

        $this->setIdentifiers(new GenericEvent([
            $product1->getWrappedObject(),
            $product2->getWrappedObject()
        ]));
    }

    public function it_should_do_nothing_if_subject_is_not_a_product(
        IdentifierGeneratorRepository $identifierGeneratorRepository,
    ) {
        $this->setIdentifier(new GenericEvent(new \stdClass()));
        $identifierGeneratorRepository->getAll()->shouldNotBeCalled();
    }

    public function it_should_do_nothing_if_subject_is_not_an_array(
        IdentifierGeneratorRepository $identifierGeneratorRepository,
    ) {
        $this->setIdentifiers(new GenericEvent(new \stdClass()));
        $identifierGeneratorRepository->getAll()->shouldNotBeCalled();
    }

    public function it_should_do_nothing_if_a_subject_is_not_a_product(
        IdentifierGeneratorRepository $identifierGeneratorRepository,
        ProductInterface $product,
    ) {
        $this->setIdentifiers(new GenericEvent([$product->getWrappedObject(), new \stdClass()]));
        $identifierGeneratorRepository->getAll()->shouldNotBeCalled();
    }

    private function getIdentifierGenerator(): IdentifierGenerator
    {
        return new IdentifierGenerator(
            IdentifierGeneratorId::fromString('2038e1c9-68ff-4833-b06f-01e42d206002'),
            IdentifierGeneratorCode::fromString('my_generator'),
            Conditions::fromArray([]),
            Structure::fromArray([FreeText::fromString('AKN')]),
            LabelCollection::fromNormalized(['fr' => 'Mon générateur']),
            Target::fromString('sku'),
            Delimiter::fromString('-'),
        );
    }
}
