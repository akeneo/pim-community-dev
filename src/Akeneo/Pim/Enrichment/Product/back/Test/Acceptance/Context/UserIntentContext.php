<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Test\Acceptance\Context;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Query\GetUserIntentsFromStandardFormat;
use Behat\Behat\Context\Context;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UserIntentContext implements Context
{
    private ?Envelope $returnedEnvelope = null;
    private ?array $expectedResult = null;

    public function __construct(
        private ExceptionContext $exceptionContext,
        private MessageBusInterface $queryMessageBus
    ) {
    }

    /**
     * @BeforeScenario
     */
    public function clean(): void
    {
        $this->returnedEnvelope = null;
        $this->expectedResult = null;
    }

    /**
     * @When /I ask to convert standard format into user intents/
     */
    public function askToConvertStandardFormatIntoUserIntents(): void
    {
        $this->dispatchToConverter([
            'family' => 'accessories',
            'associations' => [
                'PACK' => [
                    'groups' => ['RELATED'],
                    'products' => ['associated_product'],
                    'product_models' => [],
                ],
                'SUBSTITUTION' => [
                    'groups' => [],
                    'products' => [],
                    'product_models' => ['associated_product_model'],
                ],
            ],
            'quantified_associations' => [
                'bundle' => [
                    'products' => [['identifier' => 'associated_product', 'quantity' => 12]],
                    'product_models' => [['identifier' => 'associated_product_model', 'quantity' => 21]],
                ]
            ],
//            'values' => [
//                'name' => [['data' => 'Bonjour', 'scope' => null, 'locale' => null]],
//                'measurement' => [['data' => ['amount' => 10, 'unit' => 'KILOGRAM'], 'scope' => 'e-commerce', 'locale' => 'fr_FR']],
//            ],
        ]);

        $this->expectedResult = [
            new SetFamily('accessories'),
//            new SetMeasurementValue('measurement', 'e-commerce', 'fr_FR', 10, 'KILOGRAM'),
//            new SetTextValue('name', null, null, 'Bonjour'),
            new ReplaceAssociatedProducts('PACK', ['associated_product']),
            new ReplaceAssociatedProductModels('PACK', []),
            new ReplaceAssociatedGroups('PACK', ['RELATED']),
            new ReplaceAssociatedProducts('SUBSTITUTION', []),
            new ReplaceAssociatedProductModels('SUBSTITUTION', ['associated_product_model']),
            new ReplaceAssociatedGroups('SUBSTITUTION', []),
            new ReplaceAssociatedQuantifiedProducts('bundle', [new QuantifiedEntity('associated_product', 12)]),
            new ReplaceAssociatedQuantifiedProductModels('bundle', [new QuantifiedEntity('associated_product_model', 21)]),
        ];
    }

    /**
     * @When /I ask to convert standard format with an invalid family data/
     */
    public function askToConvertStandardFormatWithInvalidFamilyData(): void
    {
        $this->dispatchToConverter(['family' => ['accessories']]);
    }

    /**
     * @Then /I obtain all expected user intents/
     */
    public function obtainAllExpectedUserIntents(): void
    {
        Assert::notNull($this->returnedEnvelope, 'No envelope returned');
        Assert::notNull($this->expectedResult, 'No expected result intialized');

        // get the value that was returned by the last message handler
        $handledStamp = $this->returnedEnvelope->last(HandledStamp::class);
        Assert::notNull($handledStamp, 'The command bus does not return any result');

        Assert::eq($handledStamp->getResult(), $this->expectedResult);
    }

    private function dispatchToConverter(array $standardFormat): void
    {
        try {
            $this->returnedEnvelope = $this->queryMessageBus->dispatch(
                new GetUserIntentsFromStandardFormat($standardFormat)
            );
        } catch (\Throwable $e) {
            $this->exceptionContext->add($e);
        }
    }
}
