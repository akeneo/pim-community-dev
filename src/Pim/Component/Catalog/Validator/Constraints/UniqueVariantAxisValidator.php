<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Pim\Component\Catalog\Exception\AlreadyExistingAxisValueCombinationException;
use Pim\Component\Catalog\FamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\EntityWithFamilyVariantRepositoryInterface;
use Pim\Component\Catalog\Validator\UniqueAxesCombinationSet;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UniqueVariantAxisValidator extends ConstraintValidator
{
    /** @var EntityWithFamilyVariantAttributesProvider */
    private $axesProvider;

    /** @var EntityWithFamilyVariantRepositoryInterface */
    private $entityWithFamilyVariantRepository;

    /** @var UniqueAxesCombinationSet */
    private $uniqueAxesCombinationSet;

    /**
     * @param EntityWithFamilyVariantAttributesProvider  $axesProvider
     * @param EntityWithFamilyVariantRepositoryInterface $repository
     * @param UniqueAxesCombinationSet                   $uniqueAxesCombinationSet
     */
    public function __construct(
        EntityWithFamilyVariantAttributesProvider $axesProvider,
        EntityWithFamilyVariantRepositoryInterface $repository,
        UniqueAxesCombinationSet $uniqueAxesCombinationSet
    ) {
        $this->axesProvider = $axesProvider;
        $this->entityWithFamilyVariantRepository = $repository;
        $this->uniqueAxesCombinationSet = $uniqueAxesCombinationSet;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($entity, Constraint $constraint)
    {
        if (!$entity instanceof EntityWithFamilyVariantInterface) {
            throw new UnexpectedTypeException($constraint, EntityWithFamilyVariantInterface::class);
        }

        if (!$constraint instanceof UniqueVariantAxis) {
            throw new UnexpectedTypeException($constraint, UniqueVariantAxis::class);
        }

        if (null === $entity->getFamilyVariant()) {
            return;
        }

        $axes = $this->axesProvider->getAxes($entity);

        if (empty($axes)) {
            return;
        }

        $this->validateValueIsNotAlreadyInDatabase($entity, $axes);
        $this->validateValueWasNotAlreadyValidated($entity, $axes);
    }

    /**
     * This method builds "combinations" of the given $entityWithFamilyVariant for its $axes.
     * A combination is the concatenation of all values for an axis.
     *
     * For example, the axis is made of 2 attributes: color and size.
     * Let say we have [blue] for color and [xl] for size.
     * The combination of this entity will be "[blue],[xl]".
     *
     * This allows use to compare multiple combinations, to look for a potential duplicate.
     *
     * @param EntityWithFamilyVariantInterface $entityWithFamilyVariant
     * @param AttributeInterface[]             $axes
     *
     * @return string
     */
    private function buildAxesCombination(
        EntityWithFamilyVariantInterface $entityWithFamilyVariant,
        array $axes
    ): string {
        $combination = [];

        foreach ($axes as $axis) {
            $value = $entityWithFamilyVariant->getValue($axis->getCode());
            $stringValue = '';

            if (null !== $value) {
                $stringValue = (string)$value;
            }

            $combination[] = $stringValue;
        }

        return implode(',', $combination);
    }

    /**
     * Adds a constraint violation if there is a sibling of "$entity" with the
     * same axis combination in database.
     *
     * @param EntityWithFamilyVariantInterface $entity
     * @param AttributeInterface[]             $axes
     */
    private function validateValueIsNotAlreadyInDatabase(EntityWithFamilyVariantInterface $entity, array $axes): void
    {
        $siblings = $this->entityWithFamilyVariantRepository->findSiblings($entity);

        if (empty($siblings)) {
            return;
        }

        $siblingsCombinations = [];
        foreach ($siblings as $sibling) {
            $siblingIdentifier = $this->getEntityIdentifier($sibling);
            $siblingsCombinations[$siblingIdentifier] = $this->buildAxesCombination($sibling, $axes);
        }

        $ownCombination = $this->buildAxesCombination($entity, $axes);

        if ('' === str_replace(',', '', $ownCombination)) {
            return;
        }

        if (in_array($ownCombination, $siblingsCombinations)) {
            $alreadyInDatabaseSiblingIdentifier = array_search($ownCombination, $siblingsCombinations);

            $this->addViolation(
                $axes,
                $ownCombination,
                $entity,
                $alreadyInDatabaseSiblingIdentifier
            );
        }
    }

    /**
     * Adds a constraint violation if a sibling of "$entity" with the same axis
     * combination was already parsed.
     *
     * This means "$uniqueAxesCombinationSet" has to be stateful.
     *
     * @param EntityWithFamilyVariantInterface $entity
     * @param AttributeInterface[]             $axes
     */
    private function validateValueWasNotAlreadyValidated(EntityWithFamilyVariantInterface $entity, array $axes): void
    {
        if (null === $entity->getParent()) {
            return;
        }

        $combination = $this->buildAxesCombination($entity, $axes);

        if ('' === str_replace(',', '', $combination)) {
            return;
        }

        try {
            $this->uniqueAxesCombinationSet->addCombination($entity, $combination);
        } catch (AlreadyExistingAxisValueCombinationException $e) {
            $alreadyValidatedSiblingIdentifier = $e->getEntityIdentifier();

            $this->addViolation(
                $axes,
                $combination,
                $entity,
                $alreadyValidatedSiblingIdentifier
            );
        }
    }

    /**
     * @param array                            $axes
     * @param string                           $combination
     * @param EntityWithFamilyVariantInterface $entityWithFamilyVariant
     * @param string                           $siblingIdentifier
     */
    private function addViolation(
        array $axes,
        string $combination,
        EntityWithFamilyVariantInterface $entityWithFamilyVariant,
        string $siblingIdentifier
    ): void {
        $axesCodes = implode(',', array_map(
            function (AttributeInterface $axis) {
                return $axis->getCode();
            },
            $axes
        ));

        $message = UniqueVariantAxis::DUPLICATE_VALUE_IN_PRODUCT_MODEL;
        if ($entityWithFamilyVariant instanceof ProductInterface) {
            $message = UniqueVariantAxis::DUPLICATE_VALUE_IN_VARIANT_PRODUCT;
        }

        $this->context->buildViolation($message, [
            '%values%' => $combination,
            '%attributes%' => $axesCodes,
            '%validated_entity%' => $this->getEntityIdentifier($entityWithFamilyVariant),
            '%sibling_with_same_value%' => $siblingIdentifier,
        ])->atPath('attribute')->addViolation();
    }

    /**
     * @param EntityWithFamilyVariantInterface $entity
     *
     * @return string
     */
    private function getEntityIdentifier(EntityWithFamilyVariantInterface $entity): string
    {
        if ($entity instanceof ProductInterface) {
            return $entity->getIdentifier();
        }

        return $entity->getCode();
    }
}
