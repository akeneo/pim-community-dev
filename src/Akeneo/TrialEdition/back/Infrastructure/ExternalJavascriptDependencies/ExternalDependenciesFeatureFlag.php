<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TrialEdition\Infrastructure\ExternalJavascriptDependencies;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;

final class ExternalDependenciesFeatureFlag
{
    private FeatureFlag $trialEditionFeature;

    private string $environment;

    public function __construct(FeatureFlag $trialEditionFeature, string $environment)
    {
        $this->trialEditionFeature = $trialEditionFeature;
        $this->environment = $environment;
    }

    public function isEnabled(): bool
    {
        return $this->trialEditionFeature->isEnabled() && in_array($this->environment, ['dev', 'prod']);
    }
}
