<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Test\Acceptance\Context;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ConvertToSimpleProduct;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\SetGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\PriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetAssetValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetDateValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFileValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetImageValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMeasurementValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiReferenceEntityValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetNumberValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetPriceCollectionValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleReferenceEntityValue;
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
     * @When /I ask to convert standard format into user intents$/
     */
    public function askToConvertStandardFormatIntoUserIntents(): void
    {
        $this->dispatchToConverter([
            'parent' => null,
            'family' => 'accessories',
            'categories' => ['print', 'sales'],
            'enabled' => false,
            'groups' => ['group1', 'group2'],
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
                'a_metric' => [
                    ['data' => ['amount' => 10, 'unit' => 'KILOGRAM'], 'scope' => 'ecommerce', 'locale' => 'fr_FR'],
                    ['data' => ['amount' => '20.15', 'unit' => 'KILOGRAM'], 'scope' => 'ecommerce', 'locale' => 'en_US'],
                ],
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
            ],
        ]);

        $this->expectedResult = [
            new ConvertToSimpleProduct(),
            new SetFamily('accessories'),
            new SetCategories(['print', 'sales']),
            new SetEnabled(false),
            new SetGroups(['group1', 'group2']),
            new SetIdentifierValue('ean', '123456789'),
            new SetTextValue('name', null, null, 'Bonjour'),
            new SetTextareaValue('a_textarea', 'ecommerce', 'en_US', '<p>textarea text</p>'),
            new SetBooleanValue('a_boolean', null, 'en_US', false),
            new SetBooleanValue('a_boolean', null, 'fr_FR', true),
            new SetMeasurementValue('a_metric', 'ecommerce', 'fr_FR', 10, 'KILOGRAM'),
            new SetMeasurementValue('a_metric', 'ecommerce', 'en_US', '20.15', 'KILOGRAM'),
            new SetDateValue('a_date', 'ecommerce', 'en_US', \DateTime::createFromFormat('Y-m-d', '2020-01-01')),
            new SetFileValue('a_file', 'mobile', null, 'a/b/file.pdf'),
            new SetImageValue('an_image', 'mobile', null, 'a/b/image.png'),
            new SetNumberValue('a_number', null, null, '4.5000'),
            new SetMultiSelectValue('a_multiselect', null, null, ['codeA', 'codeB']),
            new SetSimpleSelectValue('a_simpleselect', null, 'en_US', 'the_code'),
            new SetPriceCollectionValue('a_price', null, 'en_US', [new PriceValue('10.55', 'EUR'), new PriceValue('11', 'USD')]),
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
     * @When /I ask to convert standard format into user intents with enterprise attributes/
     */
    public function askToConvertStandardFormatIntoUserIntentsEnterprise(): void
    {
        $this->dispatchToConverter([
            'parent' => null,
            'family' => 'accessories',
            'categories' => ['print', 'sales'],
            'enabled' => false,
            'groups' => ['group1', 'group2'],
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
                'a_metric' => [
                    ['data' => ['amount' => 10, 'unit' => 'KILOGRAM'], 'scope' => 'ecommerce', 'locale' => 'fr_FR'],
                    ['data' => ['amount' => '20.15', 'unit' => 'KILOGRAM'], 'scope' => 'ecommerce', 'locale' => 'en_US'],
                ],
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
                'a_record' => [['data' => 'record1', 'scope' => null, 'locale' => 'en_US']],
                'a_record_collection' => [['data' => ['record1', 'record2'], 'scope' => 'mobile', 'locale' => null]],
                'an_asset_collection' => [['data' => ['asset1', 'asset2'], 'scope' => 'mobile', 'locale' => null]],
            ],
        ]);

        $this->expectedResult = [
            new ConvertToSimpleProduct(),
            new SetFamily('accessories'),
            new SetCategories(['print', 'sales']),
            new SetEnabled(false),
            new SetGroups(['group1', 'group2']),
            new SetIdentifierValue('ean', '123456789'),
            new SetTextValue('name', null, null, 'Bonjour'),
            new SetTextareaValue('a_textarea', 'ecommerce', 'en_US', '<p>textarea text</p>'),
            new SetBooleanValue('a_boolean', null, 'en_US', false),
            new SetBooleanValue('a_boolean', null, 'fr_FR', true),
            new SetMeasurementValue('a_metric', 'ecommerce', 'fr_FR', 10, 'KILOGRAM'),
            new SetMeasurementValue('a_metric', 'ecommerce', 'en_US', '20.15', 'KILOGRAM'),
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
            new SetSimpleReferenceEntityValue('a_record', null, 'en_US', 'record1'),
            new SetMultiReferenceEntityValue('a_record_collection', 'mobile', null, ['record1', 'record2']),
            new SetAssetValue('an_asset_collection', 'mobile', null, ['asset1', 'asset2']),
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
     * @When /I ask to convert standard format with a new parent/
     */
    public function askToConvertStandardFormatWithANewParent(): void
    {
        $this->dispatchToConverter(['parent' => 'pm1']);
        $this->expectedResult = [
            new ChangeParent('pm1'),
        ];
    }

    /**
     * @When /I ask to convert standard format with an invalid parent data/
     */
    public function askToConvertStandardFormatWithInvalidParentData(): void
    {
        $this->dispatchToConverter(['parent' => ['PM']]);
    }

    /**
     * @When /I ask to convert standard format with an invalid family data/
     */
    public function askToConvertStandardFormatWithInvalidFamilyData(): void
    {
        $this->dispatchToConverter(['family' => ['accessories']]);
    }

    /**
     * @When /I ask to convert standard format with an invalid categories data/
     */
    public function askToConvertStandardFormatWithInvalidCategoryData(): void
    {
        $this->dispatchToConverter(['categories' => 'categoryA']);
    }

    /**
     * @When /I ask to convert standard format with an invalid enabled data/
     */
    public function askToConvertStandardFormatWithInvalidEnabledData(): void
    {
        $this->dispatchToConverter(['enabled' => null]);
    }

    /**
     * @When /I ask to convert standard format with an invalid associations data/
     */
    public function askToConvertStandardFormatWithInvalidAssociationsData(): void
    {
        $this->dispatchToConverter(['associations' => ['product1']]);
    }

    /**
     * @When /I ask to convert standard format with an invalid groups data/
     */
    public function askToConvertStandardFormatWithInvalidGroupsData(): void
    {
        $this->dispatchToConverter(['groups' => 'product1']);
    }

    /**
     * @When /I ask to convert standard format with an invalid boolean attribute value/
     */
    public function askToConvertStandardFormatWithInvalidBooleanAttributeValue(): void
    {
        $this->dispatchToConverter(['values' => [
            'a_boolean' => [
                ['data' => 'true', 'scope' => null, 'locale' => 'en_US'],
            ],
        ]]);
    }

    /**
     * @When /I ask to convert standard format with an invalid text attribute value/
     */
    public function askToConvertStandardFormatWithInvalidTextAttributeValue(): void
    {
        $this->dispatchToConverter(['values' => [
            'name' => [
                ['data' => ['the text'], 'scope' => null, 'locale' => 'en_US'],
            ],
        ]]);
    }

    /**
     * @When /I ask to convert standard format with an invalid textarea attribute value/
     */
    public function askToConvertStandardFormatWithInvalidTextareaAttributeValue(): void
    {
        $this->dispatchToConverter(['values' => [
            'a_textarea' => [
                ['data' => ['the text'], 'scope' => null, 'locale' => 'en_US'],
            ],
        ]]);
    }

    /**
     * @When /I ask to convert standard format with an invalid date attribute value/
     */
    public function askToConvertStandardFormatWithInvalidDateAttributeValue(): void
    {
        $this->dispatchToConverter(['values' => [
            'a_date' => [
                ['data' => 'september 10th', 'scope' => null, 'locale' => 'en_US'],
            ],
        ]]);
    }

    /**
     * @When /I ask to convert standard format with an invalid file attribute value/
     */
    public function askToConvertStandardFormatWithInvalidFileAttributeValue(): void
    {
        $this->dispatchToConverter(['values' => [
            'a_file' => [
                ['data' => ['/the/path'], 'scope' => null, 'locale' => 'en_US'],
            ],
        ]]);
    }

    /**
     * @When /I ask to convert standard format with an invalid image attribute value/
     */
    public function askToConvertStandardFormatWithInvalidImageAttributeValue(): void
    {
        $this->dispatchToConverter(['values' => [
            'an_image' => [
                ['data' => ['/the/path'], 'scope' => null, 'locale' => 'en_US'],
            ],
        ]]);
    }

    /**
     * @When /I ask to convert standard format with an invalid measurement attribute value/
     */
    public function askToConvertStandardFormatWithInvalidMeasurementAttributeValue(): void
    {
        $this->dispatchToConverter(['values' => [
            'a_metric' => [
                ['data' => 10, 'scope' => null, 'locale' => 'en_US'],
            ],
        ]]);
    }

    /**
     * @When /I ask to convert standard format with a measurement without unit/
     */
    public function askToConvertStandardFormatWithAMeasurementWithoutUnit(): void
    {
        $this->dispatchToConverter(['values' => [
            'a_metric' => [
                ['data' => ['amount' => 12], 'scope' => null, 'locale' => 'en_US'],
            ],
        ]]);
    }

    /**
     * @When /I ask to convert standard format with a measurement with empty unit/
     */
    public function askToConvertStandardFormatWithAMeasurementWithEmptyUnit(): void
    {
        $this->dispatchToConverter(['values' => [
            'a_metric' => [
                ['data' => ['amount' => 12, 'unit' => []], 'scope' => null, 'locale' => 'en_US'],
            ],
        ]]);
    }

    /**
     * @When /I ask to convert standard format with a measurement with null unit/
     */
    public function askToConvertStandardFormatWithAMeasurementWithNullUnit(): void
    {
        $this->dispatchToConverter(['values' => [
            'a_metric' => [
                ['data' => ['amount' => 12, 'unit' => null], 'scope' => null, 'locale' => 'en_US'],
            ],
        ]]);
    }

    /**
     * @When /I ask to convert standard format with an invalid simpleselect attribute value/
     */
    public function askToConvertStandardFormatWithInvalidSimpleSelectAttributeValue(): void
    {
        $this->dispatchToConverter(['values' => [
            'a_simpleselect' => [
                ['data' => ['optionA'], 'scope' => null, 'locale' => 'en_US'],
            ],
        ]]);
    }

    /**
     * @When /I ask to convert standard format with an invalid multiselect attribute value/
     */
    public function askToConvertStandardFormatWithInvalidMultiselectAttributeValue(): void
    {
        $this->dispatchToConverter(['values' => [
            'a_multiselect' => [
                ['data' => 'optionA', 'scope' => null, 'locale' => 'en_US'],
            ],
        ]]);
    }

    /**
     * @When /I ask to convert standard format with an invalid price attribute value/
     */
    public function askToConvertStandardFormatWithInvalidPriceAttributeValue(): void
    {
        $this->dispatchToConverter(['values' => [
            'a_price' => [
                ['data' => ['amount' => 10, 'currency' => 'USD'], 'scope' => null, 'locale' => 'en_US'],
            ],
        ]]);
    }

    /**
     * @When /I ask to convert standard format with an invalid table attribute value/
     */
    public function askToConvertStandardFormatWithInvalidTableAttributeValue(): void
    {
        $this->dispatchToConverter(['values' => [
            'a_table' => [
                ['data' => 'salt', 'scope' => null, 'locale' => 'en_US'],
            ],
        ]]);
    }

    /**
     * @When /I ask to convert standard format with an invalid simple reference entity attribute value/
     */
    public function askToConvertStandardFormatWithInvalidSimpleRefEntityAttributeValue(): void
    {
        $this->dispatchToConverter(['values' => [
            'a_record' => [
                ['data' => ['record1'], 'scope' => null, 'locale' => 'en_US'],
            ],
        ]]);
    }

    /**
     * @When /I ask to convert standard format with an invalid multi reference entity attribute value/
     */
    public function askToConvertStandardFormatWithInvalidMultiRefEntityAttributeValue(): void
    {
        $this->dispatchToConverter(['values' => [
            'a_record_collection' => [
                ['data' => 'record1', 'scope' => null, 'locale' => 'en_US'],
            ],
        ]]);
    }

    /**
     * @When /I ask to convert standard format with an invalid asset collection attribute value/
     */
    public function askToConvertStandardFormatWithInvalidAssetCollectionAttributeValue(): void
    {
        $this->dispatchToConverter(['values' => [
            'an_asset_collection' => [
                ['data' => 'asset1', 'scope' => null, 'locale' => 'en_US'],
            ],
        ]]);
    }

    /**
     * @Then /I obtain the expected user intent/
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
