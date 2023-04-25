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

namespace Akeneo\FreeTrial\Infrastructure\ExternalFilesDependencies;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Platform\Bundle\UIBundle\Provider\ContentSecurityPolicy\ContentSecurityPolicyProviderInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\ExternalDependencyProviderInterface;

final class Heedjy implements ExternalDependencyProviderInterface, ContentSecurityPolicyProviderInterface
{
    public function __construct(
        private FeatureFlags $featureFlags
    ) {
    }

    public function getScript(): string
    {
        return '';
    }

    /**
     * https://help.hotjar.com/hc/en-us/articles/115011640307
     */
    public function getContentSecurityPolicy(): array
    {
        if (!$this->featureFlags->isEnabled('free_trial')) {
            return [];
        }

        return [
            'img-src'     => [
                "https://heedjy-ppr.s3.fr-par.scw.cloud",
            ],
        ];
    }
}
