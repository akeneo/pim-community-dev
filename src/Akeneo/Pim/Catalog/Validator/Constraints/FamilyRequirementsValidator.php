<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Family requirements validator
 *
 * This validator will check that:
 * - every requirement must have a list of requirement for every channel,
 * - a required attribute must be an attribute of a family.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyRequirementsValidator extends ConstraintValidator
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     * @param ChannelRepositoryInterface   $channelRepository
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        ChannelRepositoryInterface $channelRepository
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->channelRepository = $channelRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($family, Constraint $constraint)
    {
        if ($family instanceof FamilyInterface) {
            $this->validateMissingChannels($family, $constraint);
            $this->validateRequiredAttributes($family, $constraint);
        }
    }

    /**
     * Validates that there is no missing channel for the family.
     *
     * @param FamilyInterface $family
     * @param Constraint      $constraint
     */
    protected function validateMissingChannels(FamilyInterface $family, Constraint $constraint)
    {
        $missingChannelCodes = $this->getMissingChannelCodes($family);
        if (0 < count($missingChannelCodes)) {
            $identifierCode = $this->attributeRepository->getIdentifierCode();
            $this->context->buildViolation(
                $constraint->messageChannel,
                [
                    '%family%'    => $family->getCode(),
                    '%id%'        => $identifierCode,
                    '%channels%'  => implode(', ', $missingChannelCodes)

                ]
            )->atPath($constraint->propertyPath)->addViolation();
        }
    }

    /**
     * Validates that every required attribute is a family attribute.
     *
     * @param FamilyInterface $family
     * @param Constraint      $constraint
     */
    protected function validateRequiredAttributes(FamilyInterface $family, Constraint $constraint)
    {
        $familyAttributeCodes = $family->getAttributeCodes();

        foreach ($family->getAttributeRequirements() as $code => $attributeRequirement) {
            if (!in_array($attributeRequirement->getAttributeCode(), $familyAttributeCodes)) {
                $this->context
                    ->buildViolation($constraint->messageAttribute, [
                        '%attribute%' => $attributeRequirement->getAttributeCode(),
                        '%channel%'   => $attributeRequirement->getChannelCode(),
                    ])
                    ->atPath($constraint->propertyPath)
                    ->addViolation();
            }
        }
    }

    /**
     * @param FamilyInterface $family
     *
     * @return string[]
     */
    protected function getMissingChannelCodes(FamilyInterface $family)
    {
        $requirements = $family->getAttributeRequirements();
        $identifierCode = $this->attributeRepository->getIdentifierCode();
        $currentChannelCodes = [];
        foreach ($requirements as $requirement) {
            if ($requirement->getAttributeCode() === $identifierCode) {
                $currentChannelCodes[] = $requirement->getChannelCode();
            }
        }

        $expectedChannelCodes = $this->channelRepository->getChannelCodes();
        $missingChannelCodes = array_diff($expectedChannelCodes, $currentChannelCodes);

        return $missingChannelCodes;
    }
}
