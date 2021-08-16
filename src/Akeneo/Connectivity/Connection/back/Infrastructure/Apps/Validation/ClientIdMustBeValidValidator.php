<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation;

use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ClientIdMustBeValidValidator extends ConstraintValidator
{
    private ClientManagerInterface $clientManager;

    public function __construct(ClientManagerInterface $clientManager)
    {
        $this->clientManager = $clientManager;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ClientIdMustBeValid) {
            throw new UnexpectedTypeException($constraint, ClientIdMustBeValid::class);
        }

        $client = $this->clientManager->findClientBy(['marketplacePublicAppId' => (string) $value]);

        if (null === $client) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
