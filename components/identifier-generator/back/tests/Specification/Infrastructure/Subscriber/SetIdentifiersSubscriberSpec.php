<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Subscriber;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\GenerateIdentifierHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\Property\GenerateFreeTextHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Match\Condition\MatchEmptyIdentifierHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Match\MatchIdentifierGeneratorHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Delimiter;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorCode;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorId;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\LabelCollection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Structure;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\TextTransformation;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Subscriber\SetIdentifiersSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\IdentifierValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\ConstraintViolation;
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
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger,
        AttributeRepositoryInterface $attributeRepository,
    ): void {
        $this->beConstructedWith(
            $identifierGeneratorRepository,
            new GenerateIdentifierHandler(new \ArrayIterator([
                new GenerateFreeTextHandler(),
            ])),
            $validator,
            $metadataFactory,
            $eventDispatcher,
            new MatchIdentifierGeneratorHandler(new \ArrayIterator([
                new MatchEmptyIdentifierHandler(),
            ])),
            $logger,
            $attributeRepository
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
        ]);
    }

    public function it_should_generate_an_identifier(
        IdentifierGeneratorRepository $identifierGeneratorRepository,
        ValidatorInterface $validator,
        MetadataFactoryInterface $metadataFactory,
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger,
        ProductInterface $product,
        ClassMetadataInterface $valueMetadata,
        PropertyMetadataInterface $valuePropertyMetadata,
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $attribute,
    ): void {
        $identifierGeneratorRepository->getAll()->shouldBeCalled()->willReturn([$this->getIdentifierGenerator()]);
        $value = IdentifierValue::value('sku', true, 'AKN');
        $product->addValue($value)->shouldBeCalled();
        $attribute->isMainIdentifier()->willReturn(true);
        $attributeRepository->findOneByIdentifier('sku')->shouldBeCalled()->willReturn($attribute);
        $product->isEnabled()->shouldBeCalled()->willReturn(true);
        $product->getFamily()->shouldBeCalled()->willReturn(null);
        $product->getCategoryCodes()->shouldBeCalled()->willReturn([]);
        $product->getValues()->shouldBeCalled()->willReturn(new WriteValueCollection([]));

        $validator->validate($product, null, ['identifiers'])
            ->shouldBeCalled()
            ->willReturn(new ConstraintViolationList([]));

        $metadataFactory->getMetadataFor($value)->shouldBeCalled()->willReturn($valueMetadata);
        $valueMetadata->getPropertyMetadata('data')->shouldBeCalled()->willReturn([$valuePropertyMetadata]);
        $constraint = new Length(null, 10);
        $valuePropertyMetadata->getConstraints()->shouldBeCalled()->willReturn([$constraint]);
        $validator->validate($value, [$constraint])->shouldBeCalled()->willReturn(new ConstraintViolationList([]));

        $logger->notice(
            '[akeneo.pim.identifier_generator] Successfully generated an identifier for the sku attribute',
            ['identifier_attribute_code' => 'sku']
        )->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::cetera())->shouldNotBeCalled();

        $this->setIdentifier(new GenericEvent($product->getWrappedObject()));
    }

    public function it_should_rollback_when_product_identifier_is_invalid(
        IdentifierGeneratorRepository $identifierGeneratorRepository,
        ValidatorInterface $validator,
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger,
        ProductInterface $product,
        MetadataFactoryInterface $metadataFactory,
        ClassMetadataInterface $valueMetadata,
        PropertyMetadataInterface $valuePropertyMetadata,
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $attribute,
    ): void {
        $attribute->isMainIdentifier()->shouldBeCalled()->willReturn(true);
        $attributeRepository->findOneByIdentifier('sku')->shouldBeCalled()->willReturn($attribute);
        $identifierGeneratorRepository->getAll()->shouldBeCalled()->willReturn([$this->getIdentifierGenerator()]);
        $value = IdentifierValue::value('sku', true, 'AKN');
        $product->addValue($value)->shouldBeCalled();
        $product->isEnabled()->shouldBeCalled()->willReturn(true);
        $product->getFamily()->shouldBeCalled()->willReturn(null);
        $product->getCategoryCodes()->shouldBeCalled()->willReturn([]);
        $product->getValues()->shouldBeCalled()->willReturn(new WriteValueCollection([]));

        $validator->validate($product, null, ['identifiers'])
            ->shouldBeCalled()
            ->willReturn(new ConstraintViolationList([
                new ConstraintViolation('', '', [], '', '', ''),
            ]));

        $metadataFactory->getMetadataFor($value)->shouldBeCalled()->willReturn($valueMetadata);
        $valueMetadata->getPropertyMetadata('data')->shouldBeCalled()->willReturn([$valuePropertyMetadata]);
        $valuePropertyMetadata->getConstraints()->shouldBeCalled()->willReturn([]);
        $validator->validate($value, [])->shouldBeCalled()->willReturn(new ConstraintViolationList([]));

        $product->removeValue($value)->shouldBeCalled();

        $eventDispatcher->dispatch(Argument::cetera())->shouldBeCalled();
        $logger->notice(Argument::cetera())->shouldNotBeCalled();

        $this->setIdentifier(new GenericEvent($product->getWrappedObject()));
    }

    public function it_should_rollback_when_product_value_is_invalid(
        IdentifierGeneratorRepository $identifierGeneratorRepository,
        ValidatorInterface $validator,
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger,
        ProductInterface $product,
        MetadataFactoryInterface $metadataFactory,
        ClassMetadataInterface $valueMetadata,
        PropertyMetadataInterface $valuePropertyMetadata,
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $attribute,
    ): void {
        $attribute->isMainIdentifier()->shouldBeCalled()->willReturn(true);
        $attributeRepository->findOneByIdentifier('sku')->shouldBeCalled()->willReturn($attribute);
        $identifierGeneratorRepository->getAll()->shouldBeCalled()->willReturn([$this->getIdentifierGenerator()]);
        $value = IdentifierValue::value('sku', true, 'AKN');
        $product->addValue($value)->shouldBeCalled();
        $product->isEnabled()->shouldBeCalled()->willReturn(true);
        $product->getFamily()->shouldBeCalled()->willReturn(null);
        $product->getCategoryCodes()->shouldBeCalled()->willReturn([]);
        $product->getValues()->shouldBeCalled()->willReturn(new WriteValueCollection([]));

        $validator->validate($product, null, ['identifiers'])
            ->shouldBeCalled()
            ->willReturn(new ConstraintViolationList([]));

        $metadataFactory->getMetadataFor($value)->shouldBeCalled()->willReturn($valueMetadata);
        $valueMetadata->getPropertyMetadata('data')->shouldBeCalled()->willReturn([$valuePropertyMetadata]);
        $valuePropertyMetadata->getConstraints()->shouldBeCalled()->willReturn([]);
        $validator->validate($value, [])->shouldBeCalled()->willReturn(new ConstraintViolationList([
            new ConstraintViolation('', '', [], '', '', ''),
        ]));

        $product->removeValue($value)->shouldBeCalled();
        $product->removeValue($value)->shouldBeCalled();

        $eventDispatcher->dispatch(Argument::cetera())->shouldBeCalled();
        $logger->notice(Argument::cetera())->shouldNotBeCalled();

        $this->setIdentifier(new GenericEvent($product->getWrappedObject()));
    }

    public function it_should_do_nothing_if_subject_is_not_a_product(
        IdentifierGeneratorRepository $identifierGeneratorRepository,
    ): void {
        $this->setIdentifier(new GenericEvent(new \stdClass()));
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
            TextTransformation::fromString('no'),
        );
    }
}
