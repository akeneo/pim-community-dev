<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validate if a product value is not null and not empty
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueCompleteValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value === null || $value === false) {
            $this->context->buildViolation($constraint->messageNotNull)
                ->addViolation();
        }

        if (!$value instanceof ProductValueInterface) {
            return;
        }

        $this->validateData($value, $constraint);
    }

    /**
     * Validate the product value data
     *
     * @param ProductValueInterface $value
     * @param Constraint            $constraint
     */
    protected function validateData(ProductValueInterface $value, Constraint $constraint)
    {
        $data = $value->getData();

        if (null === $data) {
            $this->context->buildViolation($constraint->messageNotNull)
                ->addViolation();

            return;
        }

        if ('' === $data
            || ($data instanceof Collection && 0 === count($data))) {
            $this->context->buildViolation($constraint->messageComplete)
                ->addViolation();

            return;
        }

        $this->validateComplexValue($value, $constraint);
    }

    /**
     * Validate a more complex value that doesn't store the data itself
     *
     * @param ProductValueInterface $value
     * @param Constraint            $constraint
     */
    protected function validateComplexValue(ProductValueInterface $value, Constraint $constraint)
    {
        if ($value->getAttribute()) {
            $backendType = $value->getAttribute()->getBackendType();

            if ('prices' === $backendType) {
                $this->validatePrices($value, $constraint);
            } elseif ('media' === $backendType) {
                $this->validateMedia($value, $constraint);
            } elseif ('metric' === $backendType) {
                $this->validateMetric($value, $constraint);
            }
        }
    }

    /**
     * Validate that prices contain the currencies required by the channel
     *
     * @param ProductValueInterface $value
     * @param Constraint            $constraint
     */
    protected function validatePrices(ProductValueInterface $value, Constraint $constraint)
    {
        $channel = $constraint->getChannel();

        $expectedCurrencies = array_map(
            function ($currency) {
                return $currency->getCode();
            },
            $channel->getCurrencies()->toArray()
        );
        foreach ($expectedCurrencies as $currency) {
            foreach ($value->getData() as $price) {
                if ($price->getCurrency() === $currency) {
                    if (null === $price->getData()) {
                        $this->context->buildViolation($constraint->messageComplete)
                            ->addViolation();
                    }
                }
            }
        }
    }

    /**
     * Check if the media is not empty
     *
     * @param ProductValueInterface $value
     * @param Constraint            $constraint
     */
    protected function validateMedia(ProductValueInterface $value, Constraint $constraint)
    {
        $media = $value->getMedia();
        if (!$media || $media->__toString() === '') {
            $this->context->buildViolation($constraint->messageComplete)
                ->addViolation();
        }
    }

    /**
     * Check if the metric value is not empty
     *
     * @param ProductValueInterface $value
     * @param Constraint            $constraint
     */
    protected function validateMetric(ProductValueInterface $value, Constraint $constraint)
    {
        $metric = $value->getMetric();
        if (!$metric || $metric->getData() === null) {
            $this->context->buildViolation($constraint->messageComplete)
                ->addViolation();
        }
    }
}
