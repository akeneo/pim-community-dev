<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Command\Exception;

use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LegacyViolationsException extends \LogicException
{
    public function __construct(private ConstraintViolationListInterface $constraintViolationList)
    {
        parent::__construct(
            $this->constraintViolationList instanceof ConstraintViolationList
                ? (string) $this->constraintViolationList
                : 'Some violation(s) are raised'
        );
    }

    public function violations(): ConstraintViolationListInterface
    {
        return $this->constraintViolationList;
    }
}
