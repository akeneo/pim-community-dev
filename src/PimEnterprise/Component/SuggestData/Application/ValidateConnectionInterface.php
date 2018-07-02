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

namespace PimEnterprise\Component\SuggestData\Application;

use PimEnterprise\Component\SuggestData\Command\SaveConfiguration;

/**
 * Checks that the connection to a data provider is valid.
 * For example, if the provided configuration contains a token, the validation
 * could be to check the validity of this token.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
interface ValidateConnectionInterface
{
    /**
     * @param SaveConfiguration $configuration
     *
     * @return bool
     */
    public function validate(SaveConfiguration $configuration): bool;
}
