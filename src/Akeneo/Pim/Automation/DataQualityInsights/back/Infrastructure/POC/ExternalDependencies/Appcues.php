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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\POC\ExternalDependencies;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\POC\ContentSecurityPolicyProviderInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\POC\ExternalDependencyProviderInterface;

final class Appcues implements ExternalDependencyProviderInterface, ContentSecurityPolicyProviderInterface
{
    public function getScript(): string
    {
        return '<script src="https://fast.appcues.com/86340.js"></script>';
    }

    public function getContentSecurityPolicy(): array
    {
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
