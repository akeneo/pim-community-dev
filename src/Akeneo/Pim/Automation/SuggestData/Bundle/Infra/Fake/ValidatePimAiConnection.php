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

namespace Akeneo\Pim\Automation\SuggestData\Bundle\Infra\Fake;

use Akeneo\Pim\Automation\SuggestData\Component\Application\ValidateConnectionInterface;

/**
 * Fake validation of a PIM.ai token using a hard coded value.
 * The real implementation directly tries to connect to PIM.ai with the provided token.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
final class ValidatePimAiConnection implements ValidateConnectionInterface
{
    /**
     * @const string A hard-coded token for acceptance tests.
     */
    private const PIM_AI_TOKEN = 'the-only-valid-token-for-acceptance';

    /**
     * {@inheritdoc}
     */
    public function validate(array $configurationValues): bool
    {
        return isset($configurationValues['token']) && static::PIM_AI_TOKEN === $configurationValues['token'];
    }
}
