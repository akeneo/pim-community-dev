<?php

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
final class Root
{
    /** @var Code */
    private $code;

    /** @var IdentityProvider */
    private $identityProvider;

    /** @var ServiceProvider */
    private $serviceProvider;

    public function __construct(
        Code $code,
        IdentityProvider $identityProvider,
        ServiceProvider $serviceProvider
    ) {
        $this->code = $code;
        $this->identityProvider = $identityProvider;
        $this->serviceProvider = $serviceProvider;
    }

    public static function fromArray(string $code, array $content): self
    {
        return new self(
            Code::fromString($code),
            IdentityProvider::fromArray($content['identityProvider']),
            ServiceProvider::fromArray($content['serviceProvider'])
        );
    }

    public function toArray(): array
    {
        return [
            'identityProvider' => $this->identityProvider->toArray(),
            'serviceProvider'  => $this->serviceProvider->toArray(),
        ];
    }

    public function code(): string
    {
        return $this->code->toString();
    }
}
