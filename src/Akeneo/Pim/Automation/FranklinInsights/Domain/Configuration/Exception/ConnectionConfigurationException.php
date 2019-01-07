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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Exception;

/**
 * Exception thrown when a provided suggest data configuration does not allow
 * to connect to the data provider.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
final class ConnectionConfigurationException extends \Exception
{
    /** @var int */
    private const UNPROCESSABLE_ENTITY = 422;

    /**
     * @param string $message
     */
    public function __construct(string $message = '')
    {
        parent::__construct($message, self::UNPROCESSABLE_ENTITY);
    }

    /**
     * @return ConnectionConfigurationException
     */
    public static function invalidToken(): self
    {
        return new self('akeneo_franklin_insights.connection.flash.invalid');
    }
}
