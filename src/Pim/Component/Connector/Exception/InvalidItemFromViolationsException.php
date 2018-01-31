<?php

namespace Pim\Component\Connector\Exception;

use Akeneo\Component\Batch\Item\InvalidItemException as BaseInvalidItemException;
use Akeneo\Component\Batch\Item\InvalidItemInterface;
use Pim\Component\Catalog\Model\ProductPriceInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Extends the {@link  Akeneo\Component\Batch\Item\InvalidItemException}
 * to be able to build one from a Symfony constraints violations list.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InvalidItemFromViolationsException extends BaseInvalidItemException
{
    /** @var ConstraintViolationListInterface */
    protected $violations;

    /**
     * @param ConstraintViolationListInterface|null $violations
     * @param InvalidItemInterface                  $item
     * @param array                                 $messageParameters
     * @param int                                   $code
     * @param \Exception|null                       $previous
     */
    public function __construct(
        ConstraintViolationListInterface $violations,
        InvalidItemInterface $item,
        array $messageParameters = [],
        $code = 0,
        \Exception $previous = null
    ) {
        $this->violations = $violations;

        $errors = [];
        /** @var ConstraintViolationInterface $violation */
        foreach ($violations as $violation) {
            // TODO: re-format the message, property path doesn't exist for class constraint
            // TODO: for instance cf VariantGroupAxis
            $invalidValue = $violation->getInvalidValue();
            if ($invalidValue instanceof ProductPriceInterface) {
                $invalidValue = sprintf('%s %s', $invalidValue->getData(), $invalidValue->getCurrency());
            } elseif (is_object($invalidValue) && method_exists($invalidValue, '__toString')) {
                $invalidValue = (string)$invalidValue;
            } elseif (is_object($invalidValue)) {
                $invalidValue = get_class($invalidValue);
            } elseif (is_array($invalidValue)) {
                $invalidValue = implode(', ', $invalidValue);
            }

            $propertyPath = str_replace('-<all_channels>', '', $violation->getPropertyPath());
            $propertyPath = str_replace('-<all_locales>', '', $propertyPath);

            $errors[] = sprintf(
                "%s: %s: %s\n",
                $propertyPath,
                $violation->getMessage(),
                $invalidValue
            );
        }

        parent::__construct(implode("\n", $errors), $item, $messageParameters, $code, $previous);
    }
}
