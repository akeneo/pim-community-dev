<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\Authentication\Sso\Configuration;

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
    public $identityProviderUrl;

    /** @var string */
    public $identityProviderPublicCertificate;

    /** @var string */
    public $serviceProviderEntityId;

    /** @var string */
    public $serviceProviderPublicCertificate;

    /** @var string */
    public $serviceProviderPrivateCertificate;

    public function __construct(
        string $code,
        bool $isEnabled,
        string $identityProviderEntityId,
        string $identityProviderUrl,
        string $identityProviderPublicCertificate,
        string $serviceProviderEntityId,
        string $serviceProviderPublicCertificate,
        string $serviceProviderPrivateCertificate
    ) {

        $this->code = $code;
        $this->isEnabled = $isEnabled;
        $this->identityProviderEntityId = $identityProviderEntityId;
        $this->identityProviderUrl = $identityProviderUrl;
        $this->identityProviderPublicCertificate = $identityProviderPublicCertificate;
        $this->serviceProviderEntityId = $serviceProviderEntityId;
        $this->serviceProviderPublicCertificate = $serviceProviderPublicCertificate;
        $this->serviceProviderPrivateCertificate = $serviceProviderPrivateCertificate;
    }
}
