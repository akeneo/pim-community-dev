<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\Authentication\Sso\Configuration\Application;

/**
 * DTO holding configuration data waiting to be validated and persisted.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
final class CreateOrUpdateConfiguration
{
    /** @var string */
    public $code;

    /** @var bool */
    public $isEnabled;

    /** @var string */
    public $identityProviderEntityId;

    /** @var string */
    public $identityProviderSignOnUrl;

    /** @var string */
    public $identityProviderLogoutUrl;

    /** @var string */
    public $identityProviderCertificate;

    /** @var string */
    public $serviceProviderEntityId;

    /** @var string */
    public $serviceProviderCertificate;

    /** @var string */
    public $serviceProviderPrivateKey;

    public function __construct(
        string $code,
        bool $isEnabled,
        string $identityProviderEntityId,
        string $identityProviderSignOnUrl,
        string $identityProviderLogoutUrl,
        string $identityProviderCertificate,
        string $serviceProviderEntityId,
        string $serviceProviderCertificate,
        string $serviceProviderPrivateKey
    ) {
        $this->code                        = $code;
        $this->isEnabled                   = $isEnabled;
        $this->identityProviderEntityId    = $identityProviderEntityId;
        $this->identityProviderSignOnUrl   = $identityProviderSignOnUrl;
        $this->identityProviderLogoutUrl   = $identityProviderLogoutUrl;
        $this->identityProviderCertificate = $identityProviderCertificate;
        $this->serviceProviderEntityId     = $serviceProviderEntityId;
        $this->serviceProviderCertificate  = $serviceProviderCertificate;
        $this->serviceProviderPrivateKey   = $serviceProviderPrivateKey;
    }
}
