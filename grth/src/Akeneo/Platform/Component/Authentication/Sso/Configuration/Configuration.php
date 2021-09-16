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

namespace Akeneo\Platform\Component\Authentication\Sso\Configuration;

/**
 * Dynamic configuration for SSO, composed of a root code, a section for the Identity Provider and another for
 * the Service Provider.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
final class Configuration
{
    public const DEFAULT_CODE = 'authentication_sso';

    /** @var Code */
    private $code;

    /** @var IsEnabled */
    private $isEnabled;

    /** @var IdentityProvider */
    private $identityProvider;

    /** @var ServiceProvider */
    private $serviceProvider;

    public function __construct(
        Code $code,
        IsEnabled $isEnabled,
        IdentityProvider $identityProvider,
        ServiceProvider $serviceProvider
    ) {
        $this->code = $code;
        $this->isEnabled = $isEnabled;
        $this->identityProvider = $identityProvider;
        $this->serviceProvider = $serviceProvider;
    }

    public static function fromArray(string $code, array $content): self
    {
        return new self(
            new Code($code),
            new IsEnabled($content['isEnabled']),
            IdentityProvider::fromArray($content['identityProvider']),
            ServiceProvider::fromArray($content['serviceProvider'])
        );
    }

    public function toArray(): array
    {
        return [
            'isEnabled'        => $this->isEnabled->toBoolean(),
            'identityProvider' => $this->identityProvider->toArray(),
            'serviceProvider'  => $this->serviceProvider->toArray(),
        ];
    }

    public function code(): Code
    {
        return $this->code;
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled->toBoolean();
    }
}
