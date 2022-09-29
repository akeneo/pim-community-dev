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

use Akeneo\Platform\Component\Authentication\Sso\Configuration\Application\CreateOrUpdateConfiguration;
use phpseclib3\File\X509;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates that X509 certificates are well formatted and are not expired.
 */
final class IsCertificateValidValidator extends ConstraintValidator
{
    private X509 $x509;

    public function __construct()
    {
        $this->x509 = new X509();
    }

    /**
     * {@inheritdoc}
     */
    public function validate($command, Constraint $constraint): void
    {

        // validate IDP certificate only when going from disabled to enabled to allow disabling the SSO even when certificate is outdated
        /** @var CreateOrUpdateConfiguration $command */
        $this->checkCertificate(
            $constraint->identityProviderCertificate,
            $command->identityProviderCertificate,
            $constraint,
            $command->isEnabled
        );

        $this->checkCertificate(
            $constraint->serviceProviderCertificate,
            $command->serviceProviderCertificate,
            $constraint,
            $command->isEnabled
        );
    }

    /**
     * @param $certificate string
     * @param $doValidateDate boolean
     * @return void
     */
    private function checkCertificate(
        string     $propertyPath,
        string     $certificate,
        Constraint $constraint,
        bool       $doValidateDate
    ): void {
        if (empty($certificate)) {
            return;
        }

        try {
            $result = $this->x509->loadX509($certificate);
        } catch (\Exception) {
            $result = false;
        }

        if (false === $result) {
            $this->context
                ->buildViolation($constraint->invalidMessage)
                ->atPath($propertyPath)
                ->addViolation();

            return;
        }

        if (!$doValidateDate) {
            return;
        }

        if (!$this->x509->validateDate()) {
            $this->context
                ->buildViolation($constraint->expiredMessage)
                ->atPath($propertyPath)
                ->addViolation();
        }
    }
}
