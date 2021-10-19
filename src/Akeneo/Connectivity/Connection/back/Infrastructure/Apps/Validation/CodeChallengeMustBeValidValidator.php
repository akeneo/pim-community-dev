<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AccessTokenRequest;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceApiInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CodeChallengeMustBeValidValidator extends ConstraintValidator
{
    private WebMarketplaceApiInterface $webMarketplaceApi;

    public function __construct(WebMarketplaceApiInterface $webMarketplaceApi)
    {
        $this->webMarketplaceApi = $webMarketplaceApi;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof CodeChallengeMustBeValid) {
            throw new UnexpectedTypeException($constraint, CodeChallengeMustBeValid::class);
        }

        if (!$value instanceof AccessTokenRequest) {
            throw new \LogicException(
                sprintf(
                    'Expected an instance of %s, got %s',
                    AccessTokenRequest::class,
                    get_debug_type($value)
                )
            );
        }

        $codeChallengeIsValid = $this->webMarketplaceApi->validateCodeChallenge(
            $value->getClientId(),
            $value->getCodeIdentifier(),
            $value->getCodeChallenge()
        );

        if (false === $codeChallengeIsValid) {
            $this->context->buildViolation($constraint->message)
                ->atPath('codeChallenge')
                ->addViolation();
        }
    }
}
