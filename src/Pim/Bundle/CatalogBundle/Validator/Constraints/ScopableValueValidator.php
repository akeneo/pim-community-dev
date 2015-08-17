<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for scopable product value
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScopableValueValidator extends ConstraintValidator
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $channelRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $channelRepository
     */
    public function __construct(IdentifiableObjectRepositoryInterface $channelRepository)
    {
        $this->channelRepository = $channelRepository;
    }

    /**
     * @param object     $productValue
     * @param Constraint $constraint
     */
    public function validate($productValue, Constraint $constraint)
    {
        /** @var ProductValueInterface */
        if ($productValue instanceof ProductValueInterface) {
            $isScopable = $productValue->getAttribute()->isScopable();
            $channelCode = $productValue->getScope();

            if ($isScopable && null === $channelCode) {
                $this->addExpectedScopeViolation($constraint, $productValue);
            } elseif ($isScopable && !$this->doesChannelExist($channelCode)) {
                $this->addUnexistingScopeViolation($constraint, $productValue, $channelCode);
            } elseif (!$isScopable && null !== $channelCode) {
                $this->addUnexpectedScopeViolation($constraint, $productValue);
            }
        }
    }

    /**
     * @param string $channelCode
     *
     * @return bool
     */
    protected function doesChannelExist($channelCode)
    {
        $channel = $this->channelRepository->findOneByIdentifier($channelCode);

        return null !== $channel;
    }

    /**
     * @param ScopableValue         $constraint
     * @param ProductValueInterface $value
     */
    protected function addExpectedScopeViolation(ScopableValue $constraint, ProductValueInterface $value)
    {
        $this->context->buildViolation(
            $constraint->expectedScopeMessage,
            [
                '%attribute%' => $value->getAttribute()->getCode()
            ]
        )->addViolation();
    }

    /**
     * @param ScopableValue         $constraint
     * @param ProductValueInterface $value
     * @param string                $channelCode
     */
    protected function addUnexistingScopeViolation(
        ScopableValue $constraint,
        ProductValueInterface $value,
        $channelCode
    ) {
        $this->context->buildViolation(
            $constraint->inexistingScopeMessage,
            [
                '%attribute%' => $value->getAttribute()->getCode(),
                '%channel%'   => $channelCode
            ]
        )->addViolation();
    }

    /**
     * @param ScopableValue         $constraint
     * @param ProductValueInterface $value
     */
    protected function addUnexpectedScopeViolation(ScopableValue $constraint, ProductValueInterface $value)
    {
        $this->context->buildViolation(
            $constraint->unexpectedScopeMessage,
            [
                '%attribute%' => $value->getAttribute()->getCode()
            ]
        )->addViolation();
    }
}
