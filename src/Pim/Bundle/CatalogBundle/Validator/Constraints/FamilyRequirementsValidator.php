<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Family requirements validator
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
            $missingChannelCodes = $this->getMissingChannelCodes($family);
            if (0 < count($missingChannelCodes)) {
                $identifierCode = $this->attributeRepository->getIdentifierCode();
                $this->context->buildViolation(
                    $constraint->message,
                    [
                        '%family%'    => $family->getCode(),
                        '%id%'        => $identifierCode,
                        '%channels%'  => implode(', ', $missingChannelCodes)

                    ]
                )->addViolation();
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
