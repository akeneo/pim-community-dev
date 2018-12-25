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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Validation;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Validator\ConnectionValidator as AppConnectionValidator;
use Akeneo\Tool\Bundle\BatchBundle\Item\Validator\ValidationException;
use Akeneo\Tool\Bundle\BatchBundle\Item\Validator\ValidatorInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ConnectionValidator implements ValidatorInterface
{
    /** @var AppConnectionValidator */
    private $connectionValidator;

    /**
     * @param AppConnectionValidator $connectionValidator
     */
    public function __construct(AppConnectionValidator $connectionValidator)
    {
        $this->connectionValidator = $connectionValidator;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value): void
    {
        if (true !== $this->connectionValidator->isValid()) {
            throw new ValidationException('Token is invalid or expired');
        }
    }
}
