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

namespace PimEnterprise\Component\SuggestData\Exception;

/**
 * Exception thrown when a provided suggest data configuration does not allow
 * to connect to the data provider.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
final class InvalidConnectionConfiguration extends \LogicException
{
    /**
     * @param string $code
     *
     * @return InvalidConnectionConfiguration
     */
    public static function forCode(string $code): self
    {
        return new self(sprintf('Provided configuration for connection to "%s" is invalid.', $code));
    }
}
