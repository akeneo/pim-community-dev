<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Subscriber;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\UnableToSetIdentifierException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\GenerateIdentifierCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\GenerateIdentifierHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Match\MatchIdentifierGeneratorHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Match\MatchIdentifierGeneratorQuery;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\Error;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\ErrorList;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Event\UnableToSetIdentifierEvent;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\IdentifierValue;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Mapping\ClassMetadataInterface;
use Symfony\Component\Validator\Mapping\Factory\MetadataFactoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SetIdentifiersSubscriber implements EventSubscriberInterface
{
    /** @var IdentifierGenerator[]|null */
    private ?array $identifierGenerators = null;

    public function __construct(
        private readonly IdentifierGeneratorRepository $identifierGeneratorRepository,
        private readonly GenerateIdentifierHandler $generateIdentifierCommandHandler,
        private readonly ValidatorInterface $validator,
        private readonly MetadataFactoryInterface $metadataFactory,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly MatchIdentifierGeneratorHandler $matchIdentifierGeneratorHandler,
        private readonly LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            /**
             * This event has to be executed
             * - after AddDefaultValuesSubscriber (it adds default values from parent and may set values for the
             *   computation of the match or the generation)
             * - before ComputeEntityRawValuesSubscriber (it generates the raw_values)
             */
            StorageEvents::PRE_SAVE => ['setIdentifier', 90],
        ];
    }

    public function setIdentifier(GenericEvent $event): void
    {
        $product = $event->getSubject();
        if (!$product instanceof ProductInterface) {
            return;
        }

        $productProjection = null;
        foreach ($this->getIdentifierGeneratorsByTarget() as $identifierGeneratorsByTarget) {
            foreach ($identifierGeneratorsByTarget as $identifierGenerator) {
                $productProjection = $productProjection ?? new ProductProjection(
                    $product->isEnabled(),
                    $product->getFamily()?->getCode(),
                    $this->flatValues($product),
                    $product->getCategoryCodes(),
                );
                $query = new MatchIdentifierGeneratorQuery($identifierGenerator, $productProjection);
                if (($this->matchIdentifierGeneratorHandler)($query)) {
                    try {
                        $this->setGeneratedIdentifier($identifierGenerator, $productProjection, $product);
                        $this->logger->notice(\sprintf(
                            '[akeneo.pim.identifier_generator] Successfully generated an identifier for the %s attribute',
                            $identifierGenerator->target()->asString()
                        ), ['identifier_attribute_code' => $identifierGenerator->target()->asString()]);
                    } catch (UnableToSetIdentifierException $e) {
                        $this->eventDispatcher->dispatch(new UnableToSetIdentifierEvent($e));
                    }

                    break;
                }
            }
        }
    }

    private function setGeneratedIdentifier(
        IdentifierGenerator $identifierGenerator,
        ProductProjection $productProjection,
        ProductInterface $product
    ): void {
        $command = GenerateIdentifierCommand::fromIdentifierGenerator($identifierGenerator, $productProjection);
        $newIdentifier = ($this->generateIdentifierCommandHandler)($command);

        // TODO: CPM-1106 Use real isMainIdentifier value
        $value = IdentifierValue::value($identifierGenerator->target()->asString(), true, $newIdentifier);
        $product->addValue($value);
        $product->setIdentifier($newIdentifier);

        $violations = $this->validator->validate($product, null, ['identifiers']);
        $violations->addAll($this->updatePropertyPath(
            $this->validator->validate($value, $this->getValueConstraints($value)),
            $identifierGenerator->target()->asString()
        ));

        if (\count($violations) > 0) {
            $product->removeValue($value);
            $product->setIdentifier(null);

            throw new UnableToSetIdentifierException(
                $newIdentifier,
                $identifierGenerator->target()->asString(),
                new ErrorList(\array_map(
                    fn (ConstraintViolationInterface $violation): Error => new Error(
                        (string) $violation->getMessage(),
                        $violation->getParameters(),
                        $violation->getPropertyPath()
                    ),
                    \iterator_to_array($violations)
                ))
            );
        }
    }

    /**
     * @return IdentifierGenerator[]
     */
    private function getIdentifierGenerators(): array
    {
        if (null === $this->identifierGenerators) {
            $this->identifierGenerators = $this->identifierGeneratorRepository->getAll();
        }

        return $this->identifierGenerators;
    }

    /**
     * Returns user defined constraints (Regex, max length, etc).
     *
     * @return Constraint[]
     */
    private function getValueConstraints(IdentifierValue $value): array
    {
        $metadata = $this->metadataFactory->getMetadataFor($value);
        Assert::isInstanceOf($metadata, ClassMetadataInterface::class);
        $membersMetadata = $metadata->getPropertyMetadata('data');
        $constraints = [];
        foreach ($membersMetadata as $memberMetadata) {
            $constraints = \array_merge($constraints, $memberMetadata->getConstraints());
        }

        return $constraints;
    }

    private function updatePropertyPath(
        ConstraintViolationListInterface $constraintViolationList,
        string $propertyPath
    ): ConstraintViolationListInterface {
        return new ConstraintViolationList(
            \array_map(
                fn (ConstraintViolationInterface $constraintViolation) => new ConstraintViolation(
                    $constraintViolation->getMessage(),
                    $constraintViolation->getMessageTemplate(),
                    $constraintViolation->getParameters(),
                    $constraintViolation->getRoot(),
                    $propertyPath,
                    $constraintViolation->getInvalidValue(),
                    $constraintViolation->getPlural(),
                    $constraintViolation->getCode()
                ),
                \iterator_to_array($constraintViolationList)
            )
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function flatValues(ProductInterface $product): array
    {
        return \array_map(
            static fn (ValueInterface $value) => $value->getData(),
            $product->getValues()->toArray()
        );
    }

    /**
     * @return IdentifierGenerator[][]
     */
    private function getIdentifierGeneratorsByTarget(): array
    {
        $result = [];
        foreach ($this->getIdentifierGenerators() as $identifierGenerator) {
            $target = $identifierGenerator->target()->asString();
            $result[$target][] = $identifierGenerator;
        }

        return \array_values($result);
    }
}
