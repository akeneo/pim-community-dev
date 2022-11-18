<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Subscriber;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\GenerateIdentifierHandler;
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
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Product\UniqueProductEntity;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Mapping\ClassMetadataInterface;
use Symfony\Component\Validator\Mapping\Factory\MetadataFactoryInterface;
use Symfony\Component\Validator\Mapping\PropertyMetadataInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SetIdentifiersSubscriberSpec extends ObjectBehavior
{
    public function let(
        IdentifierGeneratorRepository $identifierGeneratorRepository,
        ValidatorInterface $validator,
        MetadataFactoryInterface $metadataFactory,
    ): void {
        $this->beConstructedWith(
            $identifierGeneratorRepository,
            new GenerateIdentifierHandler(new \ArrayIterator([
                new GenerateFreeTextHandler(),
            ])),
            $validator,
            $metadataFactory
        );
    }

    public function it_should_be_an_event_subscriber(): void
    {
        $this->beAnInstanceOf(SetIdentifiersSubscriber::class);
        $this->beAnInstanceOf(EventSubscriberInterface::class);
    }

    public function it_should_listen_to_pre_save_events(): void
    {
        Assert::assertSame(\array_keys($this->getSubscribedEvents()->getWrappedObject()), [
            StorageEvents::PRE_SAVE,
            StorageEvents::PRE_SAVE_ALL,
        ]);
    }

    public function it_should_generate_an_identifier(
        IdentifierGeneratorRepository $identifierGeneratorRepository,
        ValidatorInterface $validator,
        MetadataFactoryInterface $metadataFactory,
        ProductInterface $product,
        ClassMetadataInterface $metadata,
        PropertyMetadataInterface $propertyMetadata,
    ): void {
        $product->getValue('sku')->shouldBeCalled()->willReturn(null);
        $identifierGeneratorRepository->getAll()->shouldBeCalled()->willReturn([$this->getIdentifierGenerator()]);
        $value = ScalarValue::value('sku', 'AKN');
        $product->addValue($value)->shouldBeCalled();
        $product->setIdentifier('AKN')->shouldBeCalled();

        $validator->validate($product, new UniqueProductEntity())
            ->shouldBeCalled()
            ->willReturn(new ConstraintViolationList());

        $metadataFactory->getMetadataFor($value)->shouldBeCalled()->willReturn($metadata);
        $metadata->getPropertyMetadata('data')->shouldBeCalled()->willReturn([$propertyMetadata]);
        $constraint = new Length(null, 10);
        $propertyMetadata->getConstraints()->shouldBeCalled()->willReturn([$constraint]);
        $validator->validate($value, [$constraint])->shouldBeCalled()->willReturn(new ConstraintViolationList());

        $this->setIdentifier(new GenericEvent($product->getWrappedObject()));
    }

    public function it_should_rollback_when_product_identifier_is_duplicated(
        IdentifierGeneratorRepository $identifierGeneratorRepository,
        ValidatorInterface $validator,
        ProductInterface $product,
        ConstraintViolationInterface $constraintViolation,
    ): void {
        $product->getValue('sku')->shouldBeCalled()->willReturn(null);
        $identifierGeneratorRepository->getAll()->shouldBeCalled()->willReturn([$this->getIdentifierGenerator()]);
        $value = ScalarValue::value('sku', 'AKN');
        $product->addValue($value)->shouldBeCalled();
        $product->setIdentifier('AKN')->shouldBeCalled();

        $validator->validate($product, Argument::type(UniqueProductEntity::class))
            ->shouldBeCalled()
            ->willReturn(new ConstraintViolationList([
                new ConstraintViolation('', '', [], '', '', ''),
            ]));

        $product->removeValue($value)->shouldBeCalled();
        $product->setIdentifier(null)->shouldBeCalled();

        $this->setIdentifier(new GenericEvent($product->getWrappedObject()));
    }

    public function it_should_rollback_when_product_value_is_invalid(
        IdentifierGeneratorRepository $identifierGeneratorRepository,
        ValidatorInterface $validator,
        ProductInterface $product,
        MetadataFactoryInterface $metadataFactory,
        ClassMetadataInterface $metadata,
        PropertyMetadataInterface $propertyMetadata,
    ): void {
        $product->getValue('sku')->shouldBeCalled()->willReturn(null);
        $identifierGeneratorRepository->getAll()->shouldBeCalled()->willReturn([$this->getIdentifierGenerator()]);
        $value = ScalarValue::value('sku', 'AKN');
        $product->addValue($value)->shouldBeCalled();
        $product->setIdentifier('AKN')->shouldBeCalled();

        $validator->validate($product, Argument::type(UniqueProductEntity::class))
            ->shouldBeCalled()
            ->willReturn(new ConstraintViolationList([]));

        $metadataFactory->getMetadataFor($value)->shouldBeCalled()->willReturn($metadata);
        $metadata->getPropertyMetadata('data')->shouldBeCalled()->willReturn([$propertyMetadata]);
        $propertyMetadata->getConstraints()->shouldBeCalled()->willReturn([]);
        $validator->validate($value, [])->shouldBeCalled()->willReturn(new ConstraintViolationList([
            new ConstraintViolation('', '', [], '', '', ''),
        ]));

        $product->removeValue($value)->shouldBeCalled();
        $product->setIdentifier(null)->shouldBeCalled();

        $this->setIdentifier(new GenericEvent($product->getWrappedObject()));
    }

    public function it_should_generate_several_identifiers(
        IdentifierGeneratorRepository $identifierGeneratorRepository,
        ValidatorInterface $validator,
        ProductInterface $product1,
        ProductInterface $product2,
        ConstraintViolationInterface $constraintViolation,
        MetadataFactoryInterface $metadataFactory,
        ClassMetadataInterface $metadata,
        PropertyMetadataInterface $propertyMetadata
    ): void {
        $identifierGeneratorRepository->getAll()->shouldBeCalled()->willReturn([$this->getIdentifierGenerator()]);
        $value = ScalarValue::value('sku', 'AKN');

        $product1->getValue('sku')->shouldBeCalled()->willReturn(null);
        $product1->addValue($value)->shouldBeCalled();
        $product1->setIdentifier('AKN')->shouldBeCalled();

        $validator->validate($product1, new UniqueProductEntity())
            ->shouldBeCalled()
            ->willReturn(new ConstraintViolationList());

        $metadataFactory->getMetadataFor($value)->shouldBeCalled()->willReturn($metadata);
        $metadata->getPropertyMetadata('data')->shouldBeCalled()->willReturn([$propertyMetadata]);
        $constraint = new Length(null, 10);
        $propertyMetadata->getConstraints()->shouldBeCalled()->willReturn([$constraint]);
        $validator->validate($value, [$constraint])->shouldBeCalled()->willReturn(new ConstraintViolationList());

        $product2->getValue('sku')->shouldBeCalled()->willReturn(null);
        $product2->addValue($value)->shouldBeCalled();
        $product2->setIdentifier('AKN')->shouldBeCalled();
        $validator->validate($product2, Argument::type(UniqueProductEntity::class))
            ->shouldBeCalled()
            ->willReturn(new ConstraintViolationList([
                new ConstraintViolation('', '', [], '', '', ''),
            ]));

        $product2->removeValue($value)->shouldBeCalled();
        $product2->setIdentifier(null)->shouldBeCalled();

        $this->setIdentifiers(new GenericEvent([
            $product1->getWrappedObject(),
            $product2->getWrappedObject(),
        ]));
    }

    public function it_should_do_nothing_if_subject_is_not_a_product(
        IdentifierGeneratorRepository $identifierGeneratorRepository,
    ): void {
        $this->setIdentifier(new GenericEvent(new \stdClass()));
        $identifierGeneratorRepository->getAll()->shouldNotBeCalled();
    }

    public function it_should_do_nothing_if_subject_is_not_an_array(
        IdentifierGeneratorRepository $identifierGeneratorRepository,
    ): void {
        $this->setIdentifiers(new GenericEvent(new \stdClass()));
        $identifierGeneratorRepository->getAll()->shouldNotBeCalled();
    }

    public function it_should_do_nothing_if_a_subject_is_not_a_product(
        IdentifierGeneratorRepository $identifierGeneratorRepository,
        ProductInterface $product,
    ): void {
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
