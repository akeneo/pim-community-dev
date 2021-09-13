<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
final class IsCertificateValid extends Constraint
{
    public $invalidMessage = 'This is not a valid certificate.';
    public $expiredMessage = 'This certificate has expired.';
}
