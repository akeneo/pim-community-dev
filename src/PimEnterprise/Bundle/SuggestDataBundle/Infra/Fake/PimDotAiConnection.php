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

namespace PimEnterprise\Bundle\SuggestDataBundle\Infra\Fake;

use PimEnterprise\Component\SuggestData\Application\ConnectionIsValidInterface;

/**
 * Fake validation of a PIM.ai token using a hard coded value.
 * The real implementation directly tries to connect to PIM.ai with the provided token.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
final class PimDotAiConnection implements ConnectionIsValidInterface
{
    /**
     * @const string A hard-coded token for acceptance tests.
     */
    private const PIM_DOT_AI_TOKEN = 'the-only-valid-token-for-acceptance';

    /**
     * {@inheritdoc}
     */
    public function isValid(array $configurationFields): bool
    {
        return isset($configurationFields['pim_dot_ai_activation_code']) &&
            static::PIM_DOT_AI_TOKEN === $configurationFields['pim_dot_ai_activation_code'];
    }
}
