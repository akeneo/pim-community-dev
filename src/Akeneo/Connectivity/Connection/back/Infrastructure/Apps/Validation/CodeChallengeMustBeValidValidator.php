<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AccessTokenRequest;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceApiInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
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
    private FeatureFlag $fakeAppsFeatureFlag;

    public function __construct(
        WebMarketplaceApiInterface $webMarketplaceApi,
        FeatureFlag $fakeAppsFeatureFlag
    ) {
        $this->webMarketplaceApi = $webMarketplaceApi;
        $this->fakeAppsFeatureFlag = $fakeAppsFeatureFlag;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof CodeChallengeMustBeValid) {
            throw new UnexpectedTypeException($constraint, CodeChallengeMustBeValid::class);
        }

        if (!$value instanceof AccessTokenRequest) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected an instance of %s, got %s',
                    AccessTokenRequest::class,
                    get_debug_type($value)
                )
            );
        }

        if (true === $this->fakeAppsFeatureFlag->isEnabled()) {
            return;
        }

        if (empty($value->getClientId())
            || empty($value->getCodeIdentifier())
            || empty($value->getCodeChallenge())
        ) {
            return;
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
