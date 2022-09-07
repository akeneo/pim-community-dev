<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\Validation;

use Symfony\Component\Validator\Constraint;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

final class IsCertificateValid extends Constraint
{
    public string $invalidMessage = 'This is not a valid certificate.';
    public string $expiredMessage = 'This certificate has expired.';

    public string $identityProviderCertificate;
    public string $serviceProviderCertificate;

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
