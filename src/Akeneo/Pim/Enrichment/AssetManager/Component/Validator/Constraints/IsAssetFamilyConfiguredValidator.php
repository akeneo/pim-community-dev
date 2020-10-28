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

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Validator\Constraints;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\FindAssetFamilyDetailsInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Webmozart\Assert\Assert;

/**
 * Checks if the asset family is well configured for attribute entity.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IsAssetFamilyConfiguredValidator extends ConstraintValidator
{
    /** @var array */
    protected $assetFamilyTypes;

    /** @var FindAssetFamilyDetailsInterface */
    protected $findAssetFamilyDetails;

    /**
     * @param array $assetFamilyTypes
     * @param FindAssetFamilyDetailsInterface $findAssetFamilyDetails
     */
    public function __construct(
        array $assetFamilyTypes,
        FindAssetFamilyDetailsInterface $findAssetFamilyDetails
    ) {
        $this->assetFamilyTypes = $assetFamilyTypes;
        $this->findAssetFamilyDetails = $findAssetFamilyDetails;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($attribute, Constraint $constraint)
    {
        Assert::isInstanceOf($constraint, IsAssetFamilyConfigured::class);
        $rawAssetFamilyIdentifier = $attribute->getReferenceDataName();

        if (null === $rawAssetFamilyIdentifier || '' === $rawAssetFamilyIdentifier) {
            $this->addEmptyViolation($this->context, $constraint);

            return;
        }

        try {
            $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($rawAssetFamilyIdentifier);
        } catch (\InvalidArgumentException $e) {
            $this->addInvalidViolation($constraint, $rawAssetFamilyIdentifier);

            return;
        }

        if (in_array($attribute->getType(), $this->assetFamilyTypes) &&
            null === $this->findAssetFamilyDetails->find($assetFamilyIdentifier)
        ) {
            $this->addUnknownViolation($constraint, $rawAssetFamilyIdentifier);
        }
    }

    private function addEmptyViolation(ExecutionContextInterface $context, IsAssetFamilyConfigured $constraint)
    {
        $context
            ->buildViolation($constraint->emptyMessage)
            ->atPath($constraint->propertyPath)
            ->addViolation();
    }

    private function addInvalidViolation(IsAssetFamilyConfigured $constraint, string $rawAssetFamilyIdentifier)
    {
        $this->context
            ->buildViolation($constraint->invalidMessage)
            ->setParameter('%asset_family_identifier%', $rawAssetFamilyIdentifier)
            ->atPath($constraint->propertyPath)
            ->addViolation();
    }

    private function addUnknownViolation(IsAssetFamilyConfigured $constraint, string $rawAssetFamilyIdentifier)
    {
        $this->context
            ->buildViolation($constraint->unknownMessage)
            ->setParameter('%asset_family_identifier%', $rawAssetFamilyIdentifier)
            ->atPath($constraint->propertyPath)
            ->addViolation();
    }
}
