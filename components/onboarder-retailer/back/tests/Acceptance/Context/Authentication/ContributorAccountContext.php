<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Test\Acceptance\Context\Authentication;

use Akeneo\OnboarderSerenity\Retailer\Application\Authentication\ContributorAccount\Exception\InvalidPassword;
use Akeneo\OnboarderSerenity\Retailer\Application\Authentication\ContributorAccount\UpdatePassword;
use Akeneo\OnboarderSerenity\Retailer\Application\Authentication\ContributorAccount\UpdatePasswordHandler;
use Akeneo\OnboarderSerenity\Retailer\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;
use Akeneo\OnboarderSerenity\Retailer\Infrastructure\Authentication\ContributorAccount\Repository\InMemory\InMemoryRepository;
use Akeneo\Platform\Bundle\FeatureFlagBundle\Configuration\FakeFeatureFlag;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;

final class ContributorAccountContext implements Context
{
    private array $errors;

    public function __construct(
        private InMemoryRepository $contributorAccountRepository,
        private FakeFeatureFlag $contributorAuthenticationFeatureFlag,
        private UpdatePasswordHandler $updatePasswordHandler,
    ) {
        $this->errors = [];
    }

    /**
     * @BeforeScenario @onboarder-serenity-contributor-authentication-enabled
     */
    public function enableOnboarderSerenityContributorAuthentication(): void
    {
        $this->contributorAuthenticationFeatureFlag->enable();
    }

    /**
     * @Then I should have ":contributorAccountEmails" contributor accounts
     */
    public function iShouldHaveContributorAccounts(string $contributorAccountEmails): void
    {
        $emails = explode(';', $contributorAccountEmails);

        $contributorAccount0 = $this->contributorAccountRepository->findByEmail($emails[0]);
        $contributorAccount1 = $this->contributorAccountRepository->findByEmail($emails[1]);

        Assert::assertSame($emails[0], (string) $contributorAccount0->email());
        Assert::assertSame($emails[1], (string) $contributorAccount1->email());

        $this->assertContributorAccountIsValid($contributorAccount0);
        $this->assertContributorAccountIsValid($contributorAccount1);
    }

    /**
     * @Given a contributor account with email ":email"
     */
    public function thereIsAContributorAccount(string $email): void
    {
        $this->contributorAccountRepository->save(ContributorAccount::fromEmail($email));
    }

    /**
     * @When I update the contributor account with email ":email" by updating the password to ":password"
     */
    public function iUpdateTheContributorAccountPassword(string $email, string $password): void
    {
        $contributorAccount = $this->contributorAccountRepository->findByEmail($email);
        try {
            ($this->updatePasswordHandler)(new UpdatePassword($contributorAccount->identifier(), $password));
        } catch (InvalidPassword $e) {
            $this->storeValidationErrors($e);
        }
    }

    /**
     * @Then the contributor account with email ":email" should have ":password" as password
     */
    public function theContributorAccountShouldHaveAsPassword(string $email, string $password): void
    {
        $contributorAccount = $this->contributorAccountRepository->findByEmail($email);

        Assert::assertNotNull($contributorAccount->getPassword());
    }

    /**
     * @Then I should have the following errors while validating:
     */
    public function iShouldHaveTheFollowingValidationErrors(TableNode $table): void
    {
        Assert::assertEquals($table->getHash(), $this->errors);
    }

    private function assertContributorAccountIsValid(ContributorAccount $contributorAccount): void
    {
        Assert::assertNotNull($contributorAccount->accessToken());
        Assert::assertNotNull($contributorAccount->accessTokenCreatedAt());
        Assert::assertNotNull($contributorAccount->createdAt());
    }

    private function storeValidationErrors(InvalidPassword $e): void
    {
        $errors = [];
        foreach ($e->violations() as $violation) {
            $errors[] = [
                'path' => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
            ];
        }
        $this->errors = $errors;
    }
}
