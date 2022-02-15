<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Test\Acceptance\Context;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Behat\Behat\Context\Context;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductContext implements Context
{
    /** @var ValueUserIntent[] */
    private array $valueUserIntents = [];

    public function __construct(
        private ConstraintViolationsContext $constraintViolationsContext,
        private ValidatorInterface $validator,
        private UserRepositoryInterface $userRepository
    ) {
    }

    /**
     * @BeforeScenario
     */
    public function cleanIntents(): void
    {
        $this->valueUserIntents = [];
    }

    /**
     * @Given  /^a set text value intent on the "([^"]*)" attribute with the "([^"]*)" text value$/
     */
    public function aSetTextValueIntentOnTheAttributeWithTheTextValue(string $attribute, string $text): void
    {
        $attributeInfo = \explode('-', $attribute);
        Assert::count($attributeInfo, 3);
        $channelCode = $attributeInfo[1] === 'null' ? null : $attributeInfo[1];
        $localeCode = $attributeInfo[2] === 'null' ? null : $attributeInfo[2];

        $this->valueUserIntents[] = new SetTextValue($attributeInfo[0], $localeCode, $channelCode, $text);
    }

    /**
     * @When /^the "([^"]*)" user upserts a product with the "([^"]*)" identifier$/
     * @When /^the "([^"]*)" user upserts a product with the "([^"]*)" identifier and the previous intents$/
     */
    public function theUserUpsertsAProductWithTheIdentifier(string $username, string $identifier): void
    {
        $command = new UpsertProductCommand(
            userId: $this->getUserId($username),
            productIdentifier: $identifier,
            valuesUserIntent: $this->valueUserIntents,
        );
        $this->upsertProduct($command);
    }

    /**
     * @When /^the "([^"]*)" user id upserts a product with the "([^"]*)" identifier$/
     */
    public function theUserIdUpsertsAProductWithTheIdentifier(int $userId, string $identifier): void
    {
        $command = new UpsertProductCommand(userId: $userId, productIdentifier: $identifier);
        $this->upsertProduct($command);
    }

    private function upsertProduct(UpsertProductCommand $command): void
    {
        $violations = $this->validator->validate($command);
        $this->constraintViolationsContext->add($violations);
        // @TODO later: call the handler (we cannot do that now because legacy validation is not in memory)
    }

    private function getUserId(string $username): int
    {
        if ('unknown' === $username) {
            return -10;
        }

        $user = $this->userRepository->findOneByIdentifier($username);
        Assert::notNull($user);

        return $user->getId();
    }
}
