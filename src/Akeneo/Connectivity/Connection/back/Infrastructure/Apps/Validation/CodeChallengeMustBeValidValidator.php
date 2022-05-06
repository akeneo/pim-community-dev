<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AccessTokenRequest;
use Akeneo\Connectivity\Connection\Domain\Marketplace\TestApps\Persistence\GetTestAppQueryInterface;
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
    public function __construct(
        private WebMarketplaceApiInterface $webMarketplaceApi,
        private GetTestAppQueryInterface $getTestAppQuery,
        private FeatureFlag $fakeAppsFeatureFlag
    ) {
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof CodeChallengeMustBeValid) {
            throw new UnexpectedTypeException($constraint, CodeChallengeMustBeValid::class);
        }

        if (!$value instanceof AccessTokenRequest) {
            throw new \InvalidArgumentException(
                \sprintf(
                    'Expected an instance of %s, got %s',
                    AccessTokenRequest::class,
                    \get_debug_type($value)
                )
            );
        }

        if ($this->isAccessTokenRequestEmpty($value)) {
            return;
        }

        if ($this->fakeAppsFeatureFlag->isEnabled()) {
            return;
        }

        if (!$this->isCodeChallengeValid($value)) {
            $this->context->buildViolation($constraint->message)
                ->atPath('codeChallenge')
                ->addViolation();
        }
    }

    private function isAccessTokenRequestEmpty(AccessTokenRequest $value): bool
    {
        return empty($value->getClientId())
            || empty($value->getCodeIdentifier())
            || empty($value->getCodeChallenge());
    }

    private function isCodeChallengeValid(AccessTokenRequest $value): bool
    {
        $testApp = $this->getTestAppQuery->execute($value->getClientId());

        if (null !== $testApp) {
            $secret = $testApp['secret'];
            $codeIdentifier = $value->getCodeIdentifier();
            $expectedCodeChallenge = \hash('sha256', $codeIdentifier . $secret);

            return $expectedCodeChallenge === $value->getCodeChallenge();
        }

        return $this->webMarketplaceApi->validateCodeChallenge(
            $value->getClientId(),
            $value->getCodeIdentifier(),
            $value->getCodeChallenge()
        );
    }
}
