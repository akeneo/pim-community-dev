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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Connector\Validation;

use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Query\GetConnectionStatusQuery;
use Akeneo\Tool\Bundle\BatchBundle\Item\Validator\ValidationException;
use Akeneo\Tool\Bundle\BatchBundle\Item\Validator\ValidatorInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ConnectionValidator implements ValidatorInterface
{
    /** @var GetConnectionStatusHandler */
    private $getConnectionStatusHandler;

    /**
     * @param GetConnectionStatusHandler $getConnectionStatusHandler
     */
    public function __construct(GetConnectionStatusHandler $getConnectionStatusHandler)
    {
        $this->getConnectionStatusHandler = $getConnectionStatusHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value): void
    {
        // TODO: ideally this should use a query/handler pattern
        $connectionStatus = $this->getConnectionStatusHandler->handle(new GetConnectionStatusQuery());
        if (true !== $connectionStatus->isActive()) {
            throw new ValidationException('Token is invalid or expired');
        }
    }
}
