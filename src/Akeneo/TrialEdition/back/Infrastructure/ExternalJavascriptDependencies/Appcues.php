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

use Akeneo\Platform\Bundle\UIBundle\Provider\ContentSecurityPolicy\ContentSecurityPolicyProviderInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\ExternalDependencyProviderInterface;

final class Appcues implements ExternalDependencyProviderInterface, ContentSecurityPolicyProviderInterface
{
    private const CUSTOMER_ID = 86340;

    private ExternalDependenciesFeatureFlag $featureFlag;

    public function __construct(ExternalDependenciesFeatureFlag $featureFlag)
    {
        $this->featureFlag = $featureFlag;
    }

    public function getScript(): string
    {
        if (!$this->featureFlag->isEnabled()) {
            return '';
        }

        return sprintf(
            '<script src="https://fast.appcues.com/%s.js"></script>',
            self::CUSTOMER_ID
        );
    }

    /**
     * https://docs.appcues.com/article/234-content-security-policies
     */
    public function getContentSecurityPolicy(): array
    {
        if (!$this->featureFlag->isEnabled()) {
            return [];
        }

        return [
            'frame-src'   => ["'self'", "https://*.appcues.com"],
            'style-src'   => ["'self'", "https://*.appcues.com", "https://*.appcues.net", "https://fonts.googleapis.com", "'unsafe-inline'"],
            'script-src'  => ["'self'", "https://*.appcues.com", "https://*.appcues.net", "'unsafe-inline'"],
            'img-src'     => ["'self'", "res.cloudinary.com", "twemoji.maxcdn.com"],
            'connect-src' => ["https://*.appcues.com", "*.appcues.net", "ws:"],
            'font-src'    => ["https://fonts.gstatic.com"]
        ];
    }
}
