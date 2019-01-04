<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\Validation;

use phpseclib\File\X509;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates that a X509 certificate is well formatted and has not expired.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
final class IsCertificateValidValidator extends ConstraintValidator
{
    private $x509;

    public function __construct()
    {
        $this->x509 = new X509();
    }

    /**
     * {@inheritdoc}
     */
    public function validate($certificate, Constraint $constraint)
    {
        if (null === $certificate || '' === $certificate) {
            return;
        }

        if (!$this->x509->loadX509($certificate) || !$this->x509->validateSignature(false)) {
            $this->context
                ->buildViolation($constraint->invalidMessage)
                ->addViolation()
            ;

            return;
        }

        if (!$this->x509->validateDate()) {
            $this->context
                ->buildViolation($constraint->expiredMessage)
                ->addViolation()
            ;
        }
    }
}
