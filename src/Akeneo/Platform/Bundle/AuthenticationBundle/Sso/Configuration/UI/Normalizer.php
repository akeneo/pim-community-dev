<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\UI;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\Configuration;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizes a configuration object to be sent to the UI.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
final class Normalizer implements NormalizerInterface
{
    public function normalize($object, $format = null, array $context = [])
    {
        $configArray = $object->toArray();

        return [
            'is_enabled'                           => $configArray['isEnabled'],
            'identity_provider_entity_id'          => $configArray['identityProvider']['entityId'],
            'identity_provider_sign_on_url'        => $configArray['identityProvider']['signOnUrl'],
            'identity_provider_logout_url'         => $configArray['identityProvider']['logoutUrl'],
            'identity_provider_public_certificate' => $configArray['identityProvider']['publicCertificate'],
            'service_provider_entity_id'           => $configArray['serviceProvider']['entityId'],
            'service_provider_public_certificate'  => $configArray['serviceProvider']['publicCertificate'],
            'service_provider_private_certificate' => $configArray['serviceProvider']['privateCertificate'],
        ];
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Configuration && 'internal_api' === $format;
    }
}
