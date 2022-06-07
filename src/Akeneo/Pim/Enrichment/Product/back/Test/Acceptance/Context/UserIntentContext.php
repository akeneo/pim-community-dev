<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Test\Acceptance\Context;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\PriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetDateValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFileValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetImageValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMeasurementValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetNumberValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetPriceCollectionValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTableValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
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
            'values' => [
                'ean' => [['data' => '123456789', 'scope' => null, 'locale' => null]],
                'name' => [['data' => 'Bonjour', 'scope' => null, 'locale' => null]],
                'a_textarea' => [['data' => '<p>textarea text</p>', 'scope' => 'ecommerce', 'locale' => 'en_US']],
                'a_boolean' => [
                    ['data' => false, 'scope' => null, 'locale' => 'en_US'],
                    ['data' => true, 'scope' => null, 'locale' => 'fr_FR'],
                ],
                'a_metric' => [['data' => ['amount' => 10, 'unit' => 'KILOGRAM'], 'scope' => 'ecommerce', 'locale' => 'fr_FR']],
                'a_date' => [['data' => '2020-01-01', 'scope' => 'ecommerce', 'locale' => 'en_US']],
                'a_file' => [['data' => 'a/b/file.pdf', 'scope' => 'mobile', 'locale' => null]],
                'an_image' => [['data' => 'a/b/image.png', 'scope' => 'mobile', 'locale' => null]],
                'a_number' => [['data' => '4.5000', 'scope' => null, 'locale' => null]],
                'a_multiselect' => [['data' => ['codeA', 'codeB'], 'scope' => null, 'locale' => null]],
                'a_simpleselect' => [['data' => 'the_code', 'scope' => null, 'locale' => 'en_US']],
                'a_price' => [[
                    'data' => [['amount' => '10.55', 'currency' => 'EUR'], ['amount' => '11', 'currency' => 'USD']],
                    'scope' => null,
                    'locale' => 'en_US',
                ]],
                'a_table' => [[
                    'data' => [
                        ['ingredient' => 'butter', 'quantity' => 2],
                        ['ingredient' => 'salt'],
                    ],
                    'scope' => 'mobile',
                    'locale' => null,
                ]],
            ],
        ]);

        $this->expectedResult = [
            new SetFamily('accessories'),
            new SetIdentifierValue('ean', '123456789'),
            new SetTextValue('name', null, null, 'Bonjour'),
            new SetTextareaValue('a_textarea', 'ecommerce', 'en_US', '<p>textarea text</p>'),
            new SetBooleanValue('a_boolean', null, 'en_US', false),
            new SetBooleanValue('a_boolean', null, 'fr_FR', true),
            new SetMeasurementValue('a_metric', 'ecommerce', 'fr_FR', 10, 'KILOGRAM'),
            new SetDateValue('a_date', 'ecommerce', 'en_US', \DateTime::createFromFormat('Y-m-d', '2020-01-01')),
            new SetFileValue('a_file', 'mobile', null, 'a/b/file.pdf'),
            new SetImageValue('an_image', 'mobile', null, 'a/b/image.png'),
            new SetNumberValue('a_number', null, null, '4.5000'),
            new SetMultiSelectValue('a_multiselect', null, null, ['codeA', 'codeB']),
            new SetSimpleSelectValue('a_simpleselect', null, 'en_US', 'the_code'),
            new SetPriceCollectionValue('a_price', null, 'en_US', [new PriceValue('10.55', 'EUR'), new PriceValue('11', 'USD')]),
            new SetTableValue('a_table', 'mobile', null, [
                ['ingredient' => 'butter', 'quantity' => 2],
                ['ingredient' => 'salt'],
            ]),
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

        $notFound = [];
        $expectedResult = $this->expectedResult;
        $results = $handledStamp->getResult();
        foreach ($expectedResult as $expectedResultIndex => $expectedUserIntent) {
            $found = false;
            foreach ($results as $resultIndex => $userIntent) {
                if ($userIntent == $expectedUserIntent) {
                    $found = true;
                    unset($this->expectedResult[$expectedResultIndex]);
                    unset($results[$resultIndex]);
                    break;
                }
            }
            if (!$found) {
                $notFound[] = $expectedUserIntent;
            }
        }

        Assert::isEmpty(
            $notFound,
            \sprintf("These user intents are not found:\n%s\nResults were:\n%s\n", print_r($notFound, true), print_r($handledStamp->getResult(), true))
        );
        Assert::isEmpty(
            $results,
            \sprintf("There is some extra user intent:\n%s\nResults were:\n%s", print_r($results, true), print_r($handledStamp->getResult(), true))
        );
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
