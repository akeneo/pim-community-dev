<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Test\Acceptance\Context;

use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Value\IdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Test\Acceptance\Product\InMemoryProductRepository;
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
        private InMemoryProductRepository $productRepository,
        private CategoryRepositoryInterface $categoryRepository,
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
     * @Given a product with :identifier identifier in the :categoryCode category
     */
    public function aProductWithIdentifierInTheCategory(string $identifier, string $categoryCode): void
    {
        $product = new Product();
        $product->addValue(IdentifierValue::value('sku', true, $identifier));

        $category = $this->categoryRepository->findOneByIdentifier($categoryCode);
        Assert::notNull($category);
        $product->addCategory($category);

        $this->productRepository->save($product);
    }

    /**
     * @Given /^a set text value intent on the "(?P<attribute>(?:[^"]|\\")*)" attribute with the "(?P<text>(?:[^"]|\\")*)" text value$/
     * @Given /^a set text value intent on the "(?P<attribute>(?:[^"]|\\")*)" attribute and the "(?P<locale>(?:[^"]|\\")*)" locale with the "(?P<text>(?:[^"]|\\")*)" text value$/
     * @Given /^a set text value intent on the "(?P<attribute>(?:[^"]|\\")*)" attribute and the "(?P<channel>(?:[^"]|\\")*)" channel with the "(?P<text>(?:[^"]|\\")*)" text value$/
     * @Given /^a set text value intent on the "(?P<attribute>(?:[^"]|\\")*)" attribute, the "(?P<channel>(?:[^"]|\\")*)" channel and the "(?P<locale>(?:[^"]|\\")*)" locale with the "(?P<text>(?:[^"]|\\")*)" text value$/
     */
    public function aSetTextValueIntentOnTheAttributeWithTheTextValue(string $attribute, string $text, ?string $channel = null, ?string $locale = null): void
    {
        $this->valueUserIntents[] = new SetTextValue($attribute, $channel, $locale, $text);
    }

    /**
     * @When /^the "([^"]*)" user upserts the "([^"]*)" product$/
     * @When /^the "([^"]*)" user upserts the "([^"]*)" product with the previous intents?$/
     */
    public function theUserUpsertsAProductWithTheIdentifier(string $username, string $identifier): void
    {
        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId($username),
            productIdentifier: ProductIdentifier::fromIdentifier($identifier),
            userIntents: $this->valueUserIntents,
        );
        $this->valueUserIntents = [];
        $this->upsertProduct($command);
    }

    /**
     * @When /^an unknown user tries to upsert the "([^"]*)" product$/
     */
    public function anUnknownUserTriesToUpsertAProductWithTheIdentifier(string $identifier): void
    {
        $command = UpsertProductCommand::createWithIdentifier(userId: -10, productIdentifier: ProductIdentifier::fromIdentifier($identifier), userIntents: []);
        $this->upsertProduct($command);
    }

    /**
     * @Then there is a violation saying the user is unknown
     */
    public function thereIsAMessageSayingTheUserIsUnknown(): void
    {
        $this->constraintViolationsContext->thereIsAViolationWithMessage('The "-10" user does not exist');
    }

    private function upsertProduct(UpsertProductCommand $command): void
    {
        $violations = $this->validator->validate($command);
        $this->constraintViolationsContext->add($violations);
        // @TODO later: call the handler (we cannot do that now because legacy validations are not in memory)
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
