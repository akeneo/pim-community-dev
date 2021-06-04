<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Acceptance\Context;

use Akeneo\AssetManager\Application\AssetFamily\CreateAssetFamily\CreateAssetFamilyCommand;
use Akeneo\AssetManager\Application\AssetFamily\CreateAssetFamily\CreateAssetFamilyHandler;
use Akeneo\AssetManager\Application\AssetFamily\EditAssetFamily\EditAssetFamilyCommand;
use Akeneo\AssetManager\Application\AssetFamily\EditAssetFamily\EditAssetFamilyHandler;
use Akeneo\AssetManager\Common\Fake\Anticorruption\RuleEngineValidatorACLStub;
use Akeneo\AssetManager\Common\Fake\InMemoryChannelExists;
use Akeneo\AssetManager\Common\Fake\InMemoryFindActivatedLocalesByIdentifiers;
use Akeneo\AssetManager\Common\Fake\InMemoryGetAssetCollectionTypeAdapter;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\GetAttributeIdentifierInterface;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Symfony\Command\Installer\FixturesLoader;
use Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules\ProductAttributeCannotContainAssetsException;
use Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules\ProductAttributeDoesNotExistException;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
final class EditAssetFamilyContext implements Context
{
    private const RULE_ENGINE_VALIDATION_MESSAGE = 'RULE ENGINE WILL NOT EXECUTE';
    private const ASSET_FAMILY_IDENTIFIER = 'packshot';
    private const ATTRIBUTE_CODE = 'attribute_code';
    private const UNKNOWN_CHANNEL = 'unknown_channel';
    private const UNKNOWN_LOCALE = 'UNKNOWN_LOCALE';

    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    private EditAssetFamilyHandler $editAssetFamilyHandler;

    private CreateAssetFamilyHandler $createAssetFamilyHandler;

    private ValidatorInterface $validator;

    private ConstraintViolationsContext $constraintViolationsContext;

    private InMemoryFindActivatedLocalesByIdentifiers $activatedLocales;

    private int $ruleTemplateByAssetFamilyLimit;

    private RuleEngineValidatorACLStub $ruleEngineValidatorACLStub;

    private FixturesLoader $fixturesLoader;

    private InMemoryChannelExists $channelExists;

    private InMemoryGetAssetCollectionTypeAdapter $inMemoryFindAssetCollectionTypeACL;

    private GetAttributeIdentifierInterface $getAttributeIdentifier;

    private AttributeRepositoryInterface $attributeRepository;

    public function __construct(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AttributeRepositoryInterface $attributeRepository,
        EditAssetFamilyHandler $editAssetFamilyHandler,
        CreateAssetFamilyHandler $createAssetFamilyHandler,
        ValidatorInterface $validator,
        ConstraintViolationsContext $constraintViolationsContext,
        InMemoryFindActivatedLocalesByIdentifiers $activatedLocales,
        RuleEngineValidatorACLStub $ruleEngineValidatorACLStub,
        FixturesLoader $fixturesLoader,
        InMemoryChannelExists $channelExists,
        InMemoryGetAssetCollectionTypeAdapter $inMemoryFindAssetCollectionTypeACL,
        GetAttributeIdentifierInterface $getAttributeIdentifier,
        int $ruleTemplateByAssetFamilyLimit
    ) {
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->editAssetFamilyHandler = $editAssetFamilyHandler;
        $this->createAssetFamilyHandler = $createAssetFamilyHandler;
        $this->validator = $validator;
        $this->constraintViolationsContext = $constraintViolationsContext;
        $this->activatedLocales = $activatedLocales;
        $this->ruleEngineValidatorACLStub = $ruleEngineValidatorACLStub;
        $this->fixturesLoader = $fixturesLoader;
        $this->channelExists = $channelExists;
        $this->inMemoryFindAssetCollectionTypeACL = $inMemoryFindAssetCollectionTypeACL;
        $this->ruleTemplateByAssetFamilyLimit = $ruleTemplateByAssetFamilyLimit;
        $this->inMemoryFindAssetCollectionTypeACL->stubWith(self::ASSET_FAMILY_IDENTIFIER);
        $this->getAttributeIdentifier = $getAttributeIdentifier;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @Given /^an asset family$/
     * @Given /^an asset family "designer"$/
     * @Given /^a valid asset family$/
     */
    public function theFollowingAssetFamily()
    {
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));

        $createCommand = new CreateAssetFamilyCommand(
            'designer',
            [
                'en_US' => 'Designer',
                'fr_FR' => 'Concepteur'
            ],
            [],
            []
        );

        $violations = $this->validator->validate($createCommand);
        if ($violations->count() > 0) {
            throw new \LogicException(sprintf('Cannot create asset family: %s', $violations->get(0)->getMessage()));
        }

        ($this->createAssetFamilyHandler)($createCommand);
    }

    /**
     * @When /^the user updates the asset family "([^"]*)" with:$/
     */
    public function theUserUpdatesTheAssetFamilyWith(string $identifier, TableNode $updateTable)
    {
        $updates = $updateTable->getRowsHash();
        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily($identifier);
        $command = new EditAssetFamilyCommand(
            $identifier, json_decode($updates['labels'], true), null, $attributeAsMainMedia, [], [], null
        );
        ($this->editAssetFamilyHandler)($command);
    }

    /**
     * @Then /^the asset family "([^"]*)" should be:$/
     */
    public function theAssetFamilyShouldBe(string $identifier, TableNode $assetFamilyTable)
    {
        $expectedIdentifier = AssetFamilyIdentifier::fromString($identifier);
        $expectedInformation = current($assetFamilyTable->getHash());
        $actualAssetFamily = $this->assetFamilyRepository->getByIdentifier($expectedIdentifier);
        $this->assertSameLabels(
            json_decode($expectedInformation['labels'], true),
            $actualAssetFamily
        );

        if (array_key_exists('attribute_as_label', $expectedInformation)) {
            $expectedAttributeIdentifier = sprintf('%s_%s_%s',
                $expectedInformation['attribute_as_label'],
                $actualAssetFamily->getIdentifier(),
                md5(sprintf('%s_%s', $actualAssetFamily->getIdentifier(), $expectedInformation['attribute_as_label']))
            );

            Assert::assertTrue(
                $actualAssetFamily->getAttributeAsLabelReference()->getIdentifier()->equals(
                    AttributeIdentifier::fromString($expectedAttributeIdentifier)
                )
            );
        }

        if (array_key_exists('attribute_as_main_media', $expectedInformation)) {
            $expectedAttributeIdentifier = sprintf('%s_%s_%s',
                $expectedInformation['attribute_as_main_media'],
                $actualAssetFamily->getIdentifier(),
                md5(sprintf('%s_%s', $actualAssetFamily->getIdentifier(), $expectedInformation['attribute_as_main_media']))
            );

            Assert::assertTrue(
                $actualAssetFamily->getAttributeAsMainMediaReference()->getIdentifier()->equals(
                    AttributeIdentifier::fromString($expectedAttributeIdentifier)
                )
            );
        }
    }

    private function assertSameLabels(array $expectedLabels, AssetFamily $actualAssetFamily)
    {
        $actualLabels = [];
        foreach ($actualAssetFamily->getLabelCodes() as $labelCode) {
            $actualLabels[$labelCode] = $actualAssetFamily->getLabel($labelCode);
        }

        $differences = array_merge(
            array_diff($expectedLabels, $actualLabels),
            array_diff($actualLabels, $expectedLabels)
        );

        Assert::assertEmpty(
            $differences,
            sprintf('Expected labels "%s", but found %s', json_encode($expectedLabels), json_encode($actualLabels))
        );
    }

    /**
     * @Given /^the asset family \'([^\']*)\' with the label \'([^\']*)\' equal to \'([^\']*)\'$/
     */
    public function theAssetFamilyWithTheLabelEqualTo(string $identifier, string $localCode, string $label)
    {
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));

        $label = json_decode($label);

        $createCommand = new CreateAssetFamilyCommand($identifier, [$localCode => $label], [], []);

        $violations = $this->validator->validate($createCommand);
        if ($violations->count() > 0) {
            throw new \LogicException(sprintf('Cannot create asset family: %s', $violations->get(0)->getMessage()));
        }

        ($this->createAssetFamilyHandler)($createCommand);
    }

    /**
     * @Given /^an image on an asset family \'([^\']*)\' with path \'([^\']*)\' and filename \'([^\']*)\'$/
     */
    public function anImageOnAnAssetFamilyWitPathAndFilename(string $identifier, string $filePath, string $filename): void
    {
        $createCommand = new CreateAssetFamilyCommand($identifier, [], [], []);

        $violations = $this->validator->validate($createCommand);
        if ($violations->count() > 0) {
            throw new \LogicException(sprintf('Cannot create asset family: %s', $violations->get(0)->getMessage()));
        }

        ($this->createAssetFamilyHandler)($createCommand);

        $filePath = json_decode($filePath);
        $filename = json_decode($filename);

        $file = new FileInfo();
        $file->setKey($filePath);
        $file->setOriginalFilename($filename);

        $assetFamily = $this->assetFamilyRepository->getByIdentifier(
            AssetFamilyIdentifier::fromString($identifier)
        );

        $assetFamily->updateImage(Image::fromFileInfo($file));
        $this->assetFamilyRepository->update($assetFamily);
    }

    /**
     * @When /^the user updates the image of the asset family \'([^\']*)\' with path \'([^\']*)\' and filename \'([^\']*)\'$/
     */
    public function theUserUpdatesTheImageOfTheAssetFamilyWithPathAndFilename(string $identifier, string $filePath, string $filename): void
    {
        $filePath = json_decode($filePath);
        $filename = json_decode($filename);

        $editAssetFamilyCommand = new EditAssetFamilyCommand(
            $identifier,
            [],
            [
                'filePath' => $filePath,
                'originalFilename' => $filename
            ],
            null,
            [],
            [],
            null
        );
        $this->editAssetFamily($editAssetFamilyCommand);
    }

    /**
     * @When /^the user updates the asset family \'([^\']*)\' with the label \'([^\']*)\' equal to \'([^\']*)\'$/
     */
    public function theUserUpdatesTheAssetFamilyWithTheLabelEqualTo(string $identifier, string $localCode, string $label)
    {
        $label = json_decode($label);
        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily($identifier);

        $editAssetFamilyCommand = new EditAssetFamilyCommand(
            $identifier, [$localCode => $label], null, $attributeAsMainMedia, [], [], null
        );
        $this->editAssetFamily($editAssetFamilyCommand);
    }

    /**
     * @When /^the user updates the asset family \'([^\']*)\' with an empty image$/
     */
    public function theUserUpdatesTheAssetFamilyWithAnEmptyImage(string $identifier)
    {
        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily($identifier);
        $editAssetFamilyCommand = new EditAssetFamilyCommand($identifier, [], null, $attributeAsMainMedia, [], [], null);
        $this->editAssetFamily($editAssetFamilyCommand);
    }

    /**
     * @Then /^the image of the asset family \'([^\']*)\' should be \'([^\']*)\'$/
     */
    public function theImageOfTheAssetFamilyShouldBe(string $identifier, string $filePath)
    {
        $this->constraintViolationsContext->assertThereIsNoViolations();

        $filePath = json_decode($filePath);

        $assetFamily = $this->assetFamilyRepository
            ->getByIdentifier(AssetFamilyIdentifier::fromString($identifier));

        Assert::assertEquals($filePath, $assetFamily->getImage()->getKey());
    }

    /**
     * @Then /^the asset family \'([^\']*)\' should have an empty image$/
     */
    public function theAssetFamilyShouldHaveAnEmptyImage(string $identifier)
    {
        $this->constraintViolationsContext->assertThereIsNoViolations();

        $assetFamily = $this->assetFamilyRepository->getByIdentifier(AssetFamilyIdentifier::fromString($identifier));

        $assetFamilyImage = $assetFamily->getImage();
        Assert::assertTrue($assetFamilyImage->isEmpty());
    }

    /**
     * @Given /^an empty rule template collection on the asset family \'([^\']*)\'$/
     */
    public function anEmptyRuleTemplateCollectionOnTheAssetFamily(string $code)
    {
        $this->channelExists->save(ChannelIdentifier::fromCode('ecommerce'));
        $createCommand = new CreateAssetFamilyCommand($code, [], [], []);
        $violations = $this->validator->validate($createCommand);
        if ($violations->count() > 0) {
            throw new \LogicException(sprintf('Cannot create asset family: %s', $violations->get(0)->getMessage()));
        }

        ($this->createAssetFamilyHandler)($createCommand);
    }

    /**
     * @When /^the user updates the asset family \'([^\']*)\' to set a collection of rule templates$/
     */
    public function theUserUpdatesTheAssetFamilyToSetACollectionOfRuleTemplates(string $code)
    {
        $ruleTemplate = $this->getRuleTemplate();
        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily($code);
        $editAssetFamilyCommand = new EditAssetFamilyCommand($code, [], null, $attributeAsMainMedia, [$ruleTemplate], [], null);
        $this->editAssetFamily($editAssetFamilyCommand);
    }

    /**
     * @When /^the user updates the asset family \'([^\']*)\' to set a collection of rule templates having more items than the limit$/
     */
    public function theUserUpdatesTheAssetFamilyToSetACollectionOfRuleTemplatesHavingMoreItemsThanTheLimit(string $code)
    {
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));

        $ruleTemplates = [];
        for ($i = 1; $i <= $this->ruleTemplateByAssetFamilyLimit + 1; $i++) {
            $ruleTemplates[] = $this->getRuleTemplate();
        }

        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily($code);
        $editAssetFamilyCommand = new EditAssetFamilyCommand($code, ['en_US' => ucfirst($code)], null, $attributeAsMainMedia, $ruleTemplates, [], null);
        $this->editAssetFamily($editAssetFamilyCommand);
    }

    /**
     * @Then /^the asset family \'([^\']*)\' should have the collection of rule templates$/
     */
    public function theAssetFamilyShouldHaveTheCollectionOfRuleTemplates(string $code)
    {
        $this->constraintViolationsContext->assertThereIsNoViolations();

        $expectedRuleTemplate = $this->getRuleTemplate();
        $expectedRuleTemplateCollection = RuleTemplateCollection::createFromProductLinkRules([$expectedRuleTemplate]);

        $assetFamily = $this->assetFamilyRepository
            ->getByIdentifier(AssetFamilyIdentifier::fromString($code));

        Assert::assertEquals($expectedRuleTemplateCollection, $assetFamily->getRuleTemplateCollection());
    }

    private function getRuleTemplate(): array
    {
        return [
            'product_selections' => [
                [
                    'field' => 'sku',
                    'operator' => '=',
                    'value' => '11121313'
                ]
            ],
            'assign_assets_to' => [
                [
                    'mode' => 'replace',
                    'attribute' => '1234'
                ]
            ]
        ];
    }

    /**
     * @When /^the user updates the asset family \'([^\']*)\' with a product link rule not executable by the rule engine$/
     */
    public function theUserUpdatesTheAssetFamilyWithAProductLinkRuleNotExecutableByTheRuleEngine(string $assetFamilyIdentifier)
    {
        $this->ruleEngineValidatorACLStub->stubWithViolationMessage(self::RULE_ENGINE_VALIDATION_MESSAGE);
        $invalidProductLinkRules = [['product_selections' => [['field' => 'family', 'operator' => 'IN', 'value' => 'camcorders']], 'assign_assets_to' => [['mode' => 'set', 'attribute' => 'collection']]]];
        $editAssetFamilyCommand = new EditAssetFamilyCommand(
            self::ASSET_FAMILY_IDENTIFIER, [], null, null, $invalidProductLinkRules, [], null
        );
        $this->editAssetFamily($editAssetFamilyCommand);
    }

    /**
     * @When /^the user updates an asset family "([^"]*)" with an empty product selections$/
     */
    public function theUserUpdatesAnAssetFamilyWithNoProductSelections(string $assetFamilyCode): void
    {
        $noProductSelection = [['product_selections' => [], 'assign_assets_to' => [['mode' => 'set', 'attribute' => 'collection']]]];
        $editAssetFamilyCommand = new EditAssetFamilyCommand($assetFamilyCode, [], null, null, $noProductSelection, [], null);
        $this->editAssetFamily($editAssetFamilyCommand);
    }

    /**
     * @When /^the user updates an asset family "([^"]*)" with an empty product assignment$/
     */
    public function theUserUpdatesAnAssetFamilyWithNoProductAssignment(string $assetFamilyCode): void
    {
        $noProductAssignment = [['product_selections' => [['field' => 'family', 'operator' => 'IN', 'value' => 'camcorders']], 'assign_assets_to' => []]];
        $editAssetFamilyCommand = new EditAssetFamilyCommand($assetFamilyCode, [], null, null, $noProductAssignment, [], null);
        $this->editAssetFamily($editAssetFamilyCommand);
    }

    /**
     * @Given /^an asset family with no product link rules and a text attribute$/
     */
    public function anAssetFamilyWithNoProductLinkRulesAndATextAttribute()
    {
        $this->createEcommerceChannel();
        $this->createEnUsLocale();
        $this->fixturesLoader
            ->assetFamily(self::ASSET_FAMILY_IDENTIFIER)
            ->withAttributeOfTypeText(self::ASSET_FAMILY_IDENTIFIER, self::ATTRIBUTE_CODE)
            ->load();
    }

    /**
     * @Given /^an asset family with no product link rules$/
     */
    public function anAssetFamilyWithNoProductLinkRules()
    {
        $this->createEcommerceChannel();
        $this->createEnUsLocale();
        $this->fixturesLoader->assetFamily(self::ASSET_FAMILY_IDENTIFIER)->load();
    }

    /**
     * @Given /^an asset family with no product link rules and a channel$/
     */
    public function anAssetFamilyWithNoProductLinkRulesAndAChannel()
    {
        $this->createEcommerceChannel();
        $this->fixturesLoader->assetFamily(self::ASSET_FAMILY_IDENTIFIER)->load();
    }

    /**
     * @Given /^an asset family with no product link rules and a locale$/
     */
    public function anAssetFamilyWithNoProductLinkRulesAndALocale()
    {
        $this->createEnUsLocale();
        $this->fixturesLoader->assetFamily(self::ASSET_FAMILY_IDENTIFIER)->load();
    }

    /**
     * @When /^the user updates this asset family with a dynamic product link rule having a product selection field which references this attribute$/
     */
    public function theUserCreatesAnAssetFamilyWithADynamicProductLinkRuleWhichReferencesThoseAttributes()
    {
        $dynamicRuleTemplate = [
            'product_selections' => [
                [
                    'field' => $this->toExtrapolation(self::ATTRIBUTE_CODE),
                    'operator' => '=',
                    'value' => '123456789',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ],
            'assign_assets_to' => [
                [
                    'mode' => 'replace',
                    'attribute' => 'asset_collection',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ]
        ];
        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily(self::ASSET_FAMILY_IDENTIFIER);
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, $attributeAsMainMedia, [$dynamicRuleTemplate], [], null);
        $this->editAssetFamily($command);
    }

    /**
     * @Then /^there is an asset family with a product link rule$/
     */
    public function thereIsAnAssetFamilyCreatedWithADynamicProductLinkRule(): void
    {
        $assetFamily = $this->assetFamilyRepository->getByIdentifier(AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER));
        Assert::assertFalse($assetFamily->getRuleTemplateCollection()->isEmpty());
    }

    /**
     * @When /^the user updates this asset family with a dynamic product link rule having a product selection value which references this text attribute$/
     */
    public function theUserUpdatesThisAssetFamilyWithADynamicProductLinkRuleHavingAProductSelectionValueWhichReferencesThisAttribute()
    {
        $dynamicRuleTemplate = [
            'product_selections' => [
                [
                    'field' => 'sku',
                    'operator' => '=',
                    'value' => $this->toExtrapolation(self::ATTRIBUTE_CODE),
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ],
            'assign_assets_to' => [
                [
                    'mode' => 'replace',
                    'attribute' => 'asset_collection',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ]
        ];
        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily(self::ASSET_FAMILY_IDENTIFIER);
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, $attributeAsMainMedia, [$dynamicRuleTemplate], [], null);
        $this->editAssetFamily($command);
    }

    /**
     * @When /^the user updates this asset family with a dynamic product link rule having a dynamic assignment attribute which references this text attribute$/
     */
    public function theUserUpdatesThisAssetFamilyWithADynamicProductLinkRuleHavingADynamicAssignmentAttributeValueWhichReferencesThisAttribute()
    {
        $dynamicRuleTemplate = [
            'product_selections' => [
                [
                    'field' => 'sku',
                    'operator' => '=',
                    'value' => '123456789',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ],
            'assign_assets_to' => [
                [
                    'mode' => 'replace',
                    'attribute' => $this->toExtrapolation(self::ATTRIBUTE_CODE),
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ]
        ];
        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily(self::ASSET_FAMILY_IDENTIFIER);
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, $attributeAsMainMedia, [$dynamicRuleTemplate], [], null);
        $this->editAssetFamily($command);
    }

    /**
     * @Given /^an asset family with no product link rules and a single option attribute$/
     */
    public function anAssetFamilyWithNoProductLinkRulesAndASingleOptionAttribute()
    {
        $this->fixturesLoader
            ->assetFamily(self::ASSET_FAMILY_IDENTIFIER)
            ->withAttributeOfTypeSingleOption(self::ASSET_FAMILY_IDENTIFIER, self::ATTRIBUTE_CODE)
            ->load();
    }

    /**
     * @When /^the user updates this asset family with a dynamic product link rule having a product selection value which references this single option attribute$/
     */
    public function theUserUpdatesThisAssetFamilyWithADynamicProductLinkRuleHavingAProductSelectionValueWhichReferencesThisSingleOptionAttribute()
    {
        $dynamicRuleTemplate = [
            'product_selections' => [
                [
                    'field' => 'sku',
                    'operator' => '=',
                    'value' => $this->toExtrapolation(self::ATTRIBUTE_CODE),
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ],
            'assign_assets_to' => [
                [
                    'mode' => 'replace',
                    'attribute' => 'asset_collection',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ]
        ];
        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily(self::ASSET_FAMILY_IDENTIFIER);
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, $attributeAsMainMedia, [$dynamicRuleTemplate], [], null);
        $this->editAssetFamily($command);
    }

    /**
     * @Given /^an asset family with no product link rules and a multiple option attribute$/
     */
    public function anAssetFamilyWithNoProductLinkRulesAndAMultipleOptionAttribute()
    {
        $this->fixturesLoader
            ->assetFamily(self::ASSET_FAMILY_IDENTIFIER)
            ->withAttributeOfTypeMultipleOption(self::ASSET_FAMILY_IDENTIFIER, self::ATTRIBUTE_CODE)
            ->load();
    }

    /**
     * @When /^the user updates this asset family with a dynamic product link rule having a product selection value which references this multiple option attribute$/
     */
    public function theUserUpdatesThisAssetFamilyWithADynamicProductLinkRuleHavingAProductSelectionValueWhichReferencesThisMultipleOptionAttribute()
    {
        $dynamicRuleTemplate = [
            'product_selections' => [
                [
                    'field' => 'sku',
                    'operator' => '=',
                    'value' => $this->toExtrapolation(self::ATTRIBUTE_CODE),
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ],
            'assign_assets_to' => [
                [
                    'mode' => 'replace',
                    'attribute' => 'asset_collection',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ]
        ];
        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily(self::ASSET_FAMILY_IDENTIFIER);
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, $attributeAsMainMedia, [$dynamicRuleTemplate], [], null);
        $this->editAssetFamily($command);
    }

    /**
     * @When /^the user updates this asset family with a dynamic product link rule having a product selection channel referencing this text attribute$/
     */
    public function theUserUpdatesThisAssetFamilyWithADynamicProductLinkRuleHavingAProductSelectionChannelWhichReferencesThisMultipleOptionAttribute()
    {
        $dynamicRuleTemplate = [
            'product_selections' => [
                [
                    'field' => 'sku',
                    'operator' => '=',
                    'value' => '11234567899',
                    'channel' => $this->toExtrapolation(self::ATTRIBUTE_CODE),
                    'locale' => 'en_US',
                ]
            ],
            'assign_assets_to' => [
                [
                    'mode' => 'replace',
                    'attribute' => 'asset_collection',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ]
        ];
        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily(self::ASSET_FAMILY_IDENTIFIER);
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, $attributeAsMainMedia, [$dynamicRuleTemplate], [], null);
        $this->editAssetFamily($command);
    }

    /**
     * @When /^the user updates this asset family with a dynamic product link rule having a product selection locale which references this text attribute$/
     */
    public function theUserUpdatesThisAssetFamilyWithADynamicProductLinkRuleHavingAProductSelectionLocaleWhichReferencesThisTextAttribute()
    {
        $dynamicRuleTemplate = [
            'product_selections' => [
                [
                    'field' => 'sku',
                    'operator' => '=',
                    'value' => '11234567899',
                    'channel' => 'ecommerce',
                    'locale' => $this->toExtrapolation(self::ATTRIBUTE_CODE),
                ]
            ],
            'assign_assets_to' => [
                [
                    'mode' => 'replace',
                    'attribute' => 'asset_collection',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ]
        ];
        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily(self::ASSET_FAMILY_IDENTIFIER);
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, $attributeAsMainMedia, [$dynamicRuleTemplate], [], null);
        $this->editAssetFamily($command);
    }

    /**
     * @When /^the user updates this asset family with a dynamic product link rule having a dynamic assignment channel which references this text attribute$/
     */
    public function theUserUpdatesThisAssetFamilyWithADynamicProductLinkRuleHavingADynamicAssignmentChannelWhichReferencesThisTextAttribute()
    {
        $dynamicRuleTemplate = [
            'product_selections' => [
                [
                    'field' => 'sku',
                    'operator' => '=',
                    'value' => '11234567899',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ],
            'assign_assets_to' => [
                [
                    'mode' => 'replace',
                    'attribute' => 'asset_collection',
                    'channel' => $this->toExtrapolation(self::ATTRIBUTE_CODE),
                    'locale' => 'en_US',
                ]
            ]
        ];
        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily(self::ASSET_FAMILY_IDENTIFIER);
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, $attributeAsMainMedia, [$dynamicRuleTemplate], [], null);
        $this->editAssetFamily($command);
    }

    /**
     * @When /^the user updates this asset family with a dynamic product link rule having a dynamic assignment locale which references this text attribute$/
     */
    public function theUserUpdatesThisAssetFamilyWithADynamicProductLinkRuleHavingADynamicAssignmentLocaleWhichReferencesThisTextAttribute()
    {
        $dynamicRuleTemplate = [
            'product_selections' => [
                [
                    'field' => 'sku',
                    'operator' => '=',
                    'value' => '11234567899',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ],
            'assign_assets_to' => [
                [
                    'mode' => 'replace',
                    'attribute' => 'asset_collection',
                    'channel' => 'ecommerce',
                    'locale' => $this->toExtrapolation(self::ATTRIBUTE_CODE),
                ]
            ]
        ];
        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily(self::ASSET_FAMILY_IDENTIFIER);
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, $attributeAsMainMedia, [$dynamicRuleTemplate], [], null);
        $this->editAssetFamily($command);
    }

    /**
     * @Given /^an asset family with no product link rules and an attribute with a type unsupported for extrapolation$/
     */
    public function anAssetFamilyWithNoProductLinkRulesAndAMediaFileAttribute()
    {
        $this->fixturesLoader
            ->assetFamily(self::ASSET_FAMILY_IDENTIFIER)
            ->withAttributeOfTypeMediaFile(self::ASSET_FAMILY_IDENTIFIER, self::ATTRIBUTE_CODE)
            ->load();
    }

    /**
     * @Then /^there should be a validation error stating that the product selection field does not support this attribute for extrapolation$/
     */
    public function thereShouldBeAValidationErrorStatingThatTheProductSelectionFieldDoesNotSupportExtrapolatedMediaFileAttribute()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            sprintf('The attribute "%s" of type "media_file" is not supported, only the following attribute types are supported for this field: text', self::ATTRIBUTE_CODE)
        );
    }

    /**
     * @When /^the user updates this asset family with a dynamic product link rule having a product selection value which references this attribute$/
     */
    public function theUserUpdatesThisAssetFamilyWithADynamicProductLinkRuleHavingAProductSelectionValueWhichReferencesAnAttributeHavingAnUnsupportedAttributeType()
    {
        $dynamicRuleTemplate = [
            'product_selections' => [
                [
                    'field' => 'sku',
                    'operator' => '=',
                    'value' => $this->toExtrapolation(self::ATTRIBUTE_CODE),
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ],
            'assign_assets_to' => [
                [
                    'mode' => 'replace',
                    'attribute' => 'asset_collection',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ]
        ];
        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily(self::ASSET_FAMILY_IDENTIFIER);
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, $attributeAsMainMedia, [$dynamicRuleTemplate], [], null);
        $this->editAssetFamily($command);
    }

    /**
     * @When /^there should be a validation error stating that the product selection value does not support this attribute for extrapolation/
     */
    public function there_should_be_a_validation_error_stating_that_the_product_selection_value_does_not_support_extrapolated_media_file_attribute()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            sprintf('The attribute "%s" of type "media_file" is not supported, only the following attribute types are supported for this field: text, option, option_collection', self::ATTRIBUTE_CODE)
        );
    }

    /**
     * @When /^the user updates this asset family with a dynamic product link rule having a product selection channel which references this attribute$/
     */
    public function theUserUpdatesThisAssetFamilyWithADynamicProductLinkRuleHavingAProductSelectionChannelWhichReferencesAnAttributeHavingAnUnsupportedAttributeType()
    {
        $dynamicRuleTemplate = [
            'product_selections' => [
                [
                    'field' => 'sku',
                    'operator' => '=',
                    'value' => '123444456789',
                    'channel' => $this->toExtrapolation(self::ATTRIBUTE_CODE),
                    'locale' => 'en_US',
                ]
            ],
            'assign_assets_to' => [
                [
                    'mode' => 'replace',
                    'attribute' => 'asset_collection',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ]
        ];
        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily(self::ASSET_FAMILY_IDENTIFIER);
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, $attributeAsMainMedia, [$dynamicRuleTemplate], [], null);
        $this->editAssetFamily($command);
    }

    /**
     * @Then /^there should be a validation error stating that the product selection channel does not support this attribute for extrapolation$/
     */
    public function thereShouldBeAValidationErrorStatingThatTheProductSelectionChannelDoesNotSupportExtrapolatedMediaFileAttribute()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            sprintf('The attribute "%s" of type "media_file" is not supported, only the following attribute types are supported for this field: text', self::ATTRIBUTE_CODE)
        );
    }

    /**
     * @When /^the user updates this asset family with a dynamic product link rule having a product selection locale which references this attribute$/
     */
    public function theUserUpdatesThisAssetFamilyWithADynamicProductLinkRuleHavingAProductSelectionLocaleWhichReferencesAnAttributeHavingAnUnsupportedAttributeType()
    {
        $dynamicRuleTemplate = [
            'product_selections' => [
                [
                    'field' => 'sku',
                    'operator' => '=',
                    'value' => '123444456789',
                    'channel' => 'ecommerce',
                    'locale' => $this->toExtrapolation(self::ATTRIBUTE_CODE),
                ]
            ],
            'assign_assets_to' => [
                [
                    'mode' => 'replace',
                    'attribute' => 'asset_collection',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ]
        ];
        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily(self::ASSET_FAMILY_IDENTIFIER);
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, $attributeAsMainMedia, [$dynamicRuleTemplate], [], null);
        $this->editAssetFamily($command);
    }

    /**
     * @When /^the user updates this asset family with a dynamic product link rule having an assignment attribute which references this attribute$/
     */
    public function theUserUpdatesThisAssetFamilyWithADynamicProductLinkRuleHavingAnAssignmentAttributeWhichReferencesAnAttributeHavingAnUnsupportedAttributeType()
    {
        $dynamicRuleTemplate = [
            'product_selections' => [
                [
                    'field' => 'sku',
                    'operator' => '=',
                    'value' => '123444456789',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ],
            'assign_assets_to' => [
                [
                    'mode' => 'replace',
                    'attribute' => $this->toExtrapolation(self::ATTRIBUTE_CODE),
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ]
        ];
        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily(self::ASSET_FAMILY_IDENTIFIER);
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, $attributeAsMainMedia, [$dynamicRuleTemplate], [], null);
        $this->editAssetFamily($command);
    }

    /**
     * @Then /^there should be a validation error stating that the product selection locale does not support this attribute for extrapolation$/
     */
    public function thereShouldBeAValidationErrorStatingThatTheAssignmentAttributeDoesNotSupportThisExtrapolatedAttributeType()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            sprintf('The attribute "%s" of type "media_file" is not supported, only the following attribute types are supported for this field: text', self::ATTRIBUTE_CODE)
        );
    }

    /**
     * @When /^the user updates this asset family with a dynamic product link rule having an assignment channel which references this attribute$/
     */
    public function theUserUpdatesThisAssetFamilyWithADynamicProductLinkRuleHavingAnAssignmentChannelWhichReferencesThisAttribute()
    {
        $dynamicRuleTemplate = [
            'product_selections' => [
                [
                    'field' => 'sku',
                    'operator' => '=',
                    'value' => '123444456789',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ],
            'assign_assets_to' => [
                [
                    'mode' => 'replace',
                    'attribute' => 'my_asset_collection',
                    'channel' => $this->toExtrapolation(self::ATTRIBUTE_CODE),
                    'locale' => 'en_US',
                ]
            ]
        ];
        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily(self::ASSET_FAMILY_IDENTIFIER);
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, $attributeAsMainMedia, [$dynamicRuleTemplate], [], null);
        $this->editAssetFamily($command);
    }

    /**
     * @Then /^there should be a validation error stating that the product assignment channel does not support this attribute for extrapolation$/
     */
    public function thereShouldBeAValidationErrorStatingThatTheProductAssignmentChannelDoesNotSupportThisAttributeForExtrapolation()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            sprintf('The attribute "%s" of type "media_file" is not supported, only the following attribute types are supported for this field: text', self::ATTRIBUTE_CODE)
        );
    }

    /**
     * @When /^the user updates this asset family with a dynamic product link rule having an assignment locale which references this attribute$/
     */
    public function theUserUpdatesThisAssetFamilyWithADynamicProductLinkRuleHavingAnAssignmentLocaleWhichReferencesThisAttribute()
    {
        $dynamicRuleTemplate = [
            'product_selections' => [
                [
                    'field' => 'sku',
                    'operator' => '=',
                    'value' => '123444456789',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ],
            'assign_assets_to' => [
                [
                    'mode' => 'replace',
                    'attribute' => 'my_asset_collection',
                    'channel' => 'ecommerce',
                    'locale' => $this->toExtrapolation(self::ATTRIBUTE_CODE),
                ]
            ]
        ];
        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily(self::ASSET_FAMILY_IDENTIFIER);
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, $attributeAsMainMedia, [$dynamicRuleTemplate], [], null);
        $this->editAssetFamily($command);
    }

    /**
     * @Then /^there should be a validation error stating that the product assignment locale does not support this attribute for extrapolation$/
     */
    public function thereShouldBeAValidationErrorStatingThatTheProductAssignmentLocaleDoesNotSupportThisAttributeForExtrapolation()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            sprintf('The attribute "%s" of type "media_file" is not supported, only the following attribute types are supported for this field: text', self::ATTRIBUTE_CODE)
        );
    }

    /**
     * @When /^the user updates this asset family with a product link rule having no product selection channel$/
     */
    public function theUserUpdatesThisAssetFamilyWithAProductLinkRuleHavingANoProductSelectionChannel()
    {
        $dynamicRuleTemplate = [
            'product_selections' => [
                [
                    'field' => 'sku',
                    'operator' => '=',
                    'value' => '11234567899',
                    // No channel
                    'locale' => 'en_US',
                ]
            ],
            'assign_assets_to' => [
                [
                    'mode' => 'replace',
                    'attribute' => 'asset_collection',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ]
        ];
        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily(self::ASSET_FAMILY_IDENTIFIER);
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, $attributeAsMainMedia, [$dynamicRuleTemplate], [], null);
        $this->editAssetFamily($command);
    }

    /**
     * @When /^the user updates this asset family with a product link rule having a product selection channel referencing this channel$/
     */
    public function theUserUpdatesThisAssetFamilyWithAProductLinkRuleHavingAProductSelectionChannelReferencingThisChannel()
    {
        $dynamicRuleTemplate = [
            'product_selections' => [
                [
                    'field' => 'sku',
                    'operator' => '=',
                    'value' => '11234567899',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ],
            ],
            'assign_assets_to' => [
                [
                    'mode' => 'replace',
                    'attribute' => 'asset_collection',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ],
            ],
        ];
        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily(self::ASSET_FAMILY_IDENTIFIER);
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, $attributeAsMainMedia, [$dynamicRuleTemplate], [], null);
        $this->editAssetFamily($command);
    }

    /**
     * @Then /^there should be a validation error stating that the product selection channel does not exist$/
     */
    public function thereShouldBeAValidationErrorStatingThatTheProductSelectionChannelDoesNotExist()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            sprintf('The channel "%s" does not exist', self::UNKNOWN_CHANNEL)
        );
    }

    /**
     * @When /^the user updates this asset family with a product link rule having a product selection channel that does not exist$/
     */
    public function theUserUpdatesThisAssetFamilyWithAProductLinkRuleHavingAProductSelectionChannelThatDoesNotExist()
    {
        $dynamicRuleTemplate = [
            'product_selections' => [
                [
                    'field' => 'sku',
                    'operator' => '=',
                    'value' => '11234567899',
                    'channel' => self::UNKNOWN_CHANNEL,
                    'locale' => 'en_US',
                ],
            ],
            'assign_assets_to' => [
                [
                    'mode' => 'replace',
                    'attribute' => 'asset_collection',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ],
            ],
        ];
        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily(self::ASSET_FAMILY_IDENTIFIER);
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, $attributeAsMainMedia, [$dynamicRuleTemplate], [], null);
        $this->editAssetFamily($command);
    }

    /**
     * @When /^the user updates this asset family with a product link rule having no product selection locale$/
     */
    public function theUserUpdatesThisAssetFamilyWithAProductLinkRuleHavingANoProductSelectionLocale()
    {
        $dynamicRuleTemplate = [
            'product_selections' => [
                [
                    'field' => 'sku',
                    'operator' => '=',
                    'value' => '11234567899',
                    'channel' => 'ecommerce',
                ],
            ],
            'assign_assets_to' => [
                [
                    'mode' => 'replace',
                    'attribute' => 'asset_collection',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ],
            ],
        ];
        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily(self::ASSET_FAMILY_IDENTIFIER);
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, $attributeAsMainMedia, [$dynamicRuleTemplate], [], null);
        $this->editAssetFamily($command);
    }

    /**
     * @When /^the user updates this asset family with a product link rule having a product selection locale referencing this locale$/
     */
    public function theUserUpdatesThisAssetFamilyWithAProductLinkRuleHavingAProductSelectionLocaleReferencingThisLocale()
    {
        $dynamicRuleTemplate = [
            'product_selections' => [
                [
                    'field' => 'sku',
                    'operator' => '=',
                    'value' => '11234567899',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ],
            ],
            'assign_assets_to' => [
                [
                    'mode' => 'replace',
                    'attribute' => 'asset_collection',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ],
            ],
        ];
        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily(self::ASSET_FAMILY_IDENTIFIER);
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, $attributeAsMainMedia, [$dynamicRuleTemplate], [], null);
        $this->editAssetFamily($command);
    }

    /**
     * @When /^the user updates this asset family with a product link rule having a product selection locale that does not exist$/
     */
    public function theUserUpdatesThisAssetFamilyWithAProductLinkRuleHavingAProductSelectionLocaleThatDoesNotExist()
    {
        $productLinkRule = [
            'product_selections' => [
                [
                    'field' => 'sku',
                    'operator' => '=',
                    'value' => '11234567899',
                    'channel' => 'ecommerce',
                    'locale' => self::UNKNOWN_LOCALE,
                ],
            ],
            'assign_assets_to' => [
                [
                    'mode' => 'replace',
                    'attribute' => 'asset_collection',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ],
            ],
        ];
        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily(self::ASSET_FAMILY_IDENTIFIER);
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, $attributeAsMainMedia, [$productLinkRule], [], null);
        $this->editAssetFamily($command);
    }

    /**
     * @Then /^there should be a validation error stating that the product selection locale does not exist$/
     */
    public function thereShouldBeAValidationErrorStatingThatTheProductSelectionLocaleDoesNotExist()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            sprintf('The locale "%s" is not activated or does not exist', self::UNKNOWN_LOCALE)
        );
    }

    /**
     * @When /^the user updates this asset family with a product link rule having no assignment channel$/
     */
    public function theUserUpdatesThisAssetFamilyWithAProductLinkRuleHavingNoAssignmentChannel()
    {
        $productLinkRule = [
            'product_selections' => [
                [
                    'field' => 'sku',
                    'operator' => '=',
                    'value' => '11234567899',
                ],
            ],
            'assign_assets_to' => [
                [
                    'mode' => 'replace',
                    'attribute' => 'asset_collection',
                    'channel' => 'ecommerce',
                ],
            ],
        ];
        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily(self::ASSET_FAMILY_IDENTIFIER);
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, $attributeAsMainMedia, [$productLinkRule], [], null);
        $this->editAssetFamily($command);
    }

    /**
     * @When /^the user updates this asset family with a product link rule having an assignment channel referencing this channel$/
     */
    public function theUserUpdatesThisAssetFamilyWithAProductLinkRuleHavingAnAssignmentChannelReferencingThisChannel()
    {
        $productLinkRule = [
            'product_selections' => [
                [
                    'field' => 'sku',
                    'operator' => '=',
                    'value' => '11234567899',
                ],
            ],
            'assign_assets_to' => [
                [
                    'mode' => 'replace',
                    'attribute' => 'asset_collection',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US'
                ],
            ],
        ];
        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily(self::ASSET_FAMILY_IDENTIFIER);
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, $attributeAsMainMedia, [$productLinkRule], [], null);
        $this->editAssetFamily($command);
    }

    /**
     * @When /^the user updates this asset family with a product link rule having a assignment channel that does not exist$/
     */
    public function theUserUpdatesThisAssetFamilyWithAProductLinkRuleHavingAAssignmentChannelThatDoesNotExist()
    {
        $productLinkRule = [
            'product_selections' => [
                [
                    'field' => 'sku',
                    'operator' => '=',
                    'value' => '11234567899',
                ],
            ],
            'assign_assets_to' => [
                [
                    'mode' => 'replace',
                    'attribute' => 'asset_collection',
                    'channel' => self::UNKNOWN_CHANNEL,
                    'locale' => 'en_US'
                ],
            ],
        ];
        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily(self::ASSET_FAMILY_IDENTIFIER);
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, $attributeAsMainMedia, [$productLinkRule], [], null);
        $this->editAssetFamily($command);
    }

    /**
     * @Then /^there should be a validation error stating that the assignment channel does not exist$/
     */
    public function thereShouldBeAValidationErrorStatingThatTheAssignmentChannelDoesNotExist()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            sprintf('The channel "%s" does not exist', self::UNKNOWN_CHANNEL)
        );
    }

    /**
     * @When /^the user updates this asset family with a product link rule having no assignment locale$/
     */
    public function theUserUpdatesThisAssetFamilyWithAProductLinkRuleHavingNoAssignmentLocale()
    {
        $productLinkRule = [
            'product_selections' => [
                [
                    'field' => 'sku',
                    'operator' => '=',
                    'value' => '11234567899',
                ]
            ],
            'assign_assets_to' => [
                [
                    'mode' => 'replace',
                    'attribute' => 'asset_collection',
                ]
            ]
        ];
        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily(self::ASSET_FAMILY_IDENTIFIER);
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, $attributeAsMainMedia, [$productLinkRule], [], null);
        $this->editAssetFamily($command);
    }

    /**
     * @When /^the user updates this asset family with a product link rule having an assignment locale referencing this locale$/
     */
    public function theUserUpdatesThisAssetFamilyWithAProductLinkRuleHavingAnAssignmentLocaleReferencingThisLocale()
    {
        $productLinkRule = [
            'product_selections' => [
                [
                    'field' => 'sku',
                    'operator' => '=',
                    'value' => '11234567899',
                ]
            ],
            'assign_assets_to' => [
                [
                    'mode' => 'replace',
                    'attribute' => 'asset_collection',
                    'locale' => 'en_US'
                ]
            ]
        ];
        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily(self::ASSET_FAMILY_IDENTIFIER);
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, $attributeAsMainMedia, [$productLinkRule], [], null);
        $this->editAssetFamily($command);
    }

    /**
     * @When /^the user updates this asset family with a product link rule having an assignment locale that does not exist$/
     */
    public function theUserUpdatesThisAssetFamilyWithAProductLinkRuleHavingAnAssignmentLocaleThatDoesNotExist()
    {
        $productLinkRule = [
            'product_selections' => [
                [
                    'field' => 'sku',
                    'operator' => '=',
                    'value' => '11234567899',
                ]
            ],
            'assign_assets_to' => [
                [
                    'mode' => 'replace',
                    'attribute' => 'asset_collection',
                    'locale' => self::UNKNOWN_LOCALE
                ]
            ]
        ];
        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily(self::ASSET_FAMILY_IDENTIFIER);
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, $attributeAsMainMedia, [$productLinkRule], [], null);
        $this->editAssetFamily($command);
    }

    /**
     * @Then /^there should be a validation error stating that the assignment locale does not exist$/
     */
    public function thereShouldBeAValidationErrorStatingThatTheAssignmentLocaleDoesNotExist()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            sprintf('The locale "%s" is not activated or does not exist', self::UNKNOWN_LOCALE)
        );
    }

    /**
     * @Given /^an asset family with no product link rules and an attribute with one value per channel$/
     */
    public function anAssetFamilyWithNoProductLinkRulesAndAScopableTextAttribute()
    {
        $this->createEcommerceChannel();
        $this->createEnUsLocale();
        $this->fixturesLoader
            ->assetFamily(self::ASSET_FAMILY_IDENTIFIER)
            ->withAttributeOfTypeText(self::ASSET_FAMILY_IDENTIFIER, self::ATTRIBUTE_CODE, true)
            ->load();
    }

    /**
     * @Then /^there should be a validation error stating that this attribute is not supported for extrapolation because it has one value per channel$/
     */
    public function thereShouldBeAValidationErrorStatingThatThisAttributeIsNotSupportedForExtrapolationBecauseItIsScopable()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            sprintf('The attribute "%s" cannot be used for extrapolation because it has one value per channel', self::ATTRIBUTE_CODE)
        );
    }

    /**
     * @Given /^an asset family with no product link rules and an attribute with one value per locale$/
     */
    public function anAssetFamilyWithNoProductLinkRulesAndALocalizableTextAttribute()
    {
        $this->createEcommerceChannel();
        $this->createEnUsLocale();
        $this->fixturesLoader
            ->assetFamily(self::ASSET_FAMILY_IDENTIFIER)
            ->withAttributeOfTypeText(self::ASSET_FAMILY_IDENTIFIER, self::ATTRIBUTE_CODE, false, true)
            ->load();
    }

    /**
     * @Then /^there should be a validation error stating that this attribute is not supported for extrapolation because it has one value per locale$/
     */
    public function thereShouldBeAValidationErrorStatingThatThisAttributeIsNotSupportedForExtrapolationBecauseItIsLocalizable()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            sprintf('The attribute "%s" cannot be used for extrapolation because it has one value per locale', self::ATTRIBUTE_CODE)
        );
    }

    /**
     * @When /^the user updates this asset family with a product link rule having "([^"]*)" assignment mode$/
     */
    public function theUserUpdatesThisAssetFamilyWithAProductLinkRuleHavingAssignmentMode(string $mode)
    {
        $dynamicRuleTemplate = [
            'product_selections' => [
                [
                    'field' => self::ATTRIBUTE_CODE,
                    'operator' => '=',
                    'value' => '123456789',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ],
            'assign_assets_to' => [
                [
                    'mode' => $mode,
                    'attribute' => 'asset_collection',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ]
        ];
        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily(self::ASSET_FAMILY_IDENTIFIER);
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, $attributeAsMainMedia, [$dynamicRuleTemplate], [], null);
        $this->editAssetFamily($command);
    }

    /**
     * @When /^the user updates an asset family "([^"]*)" with a product selection field "([^"]*)" and channel$/
     */
    public function theUserUpdatesAnAssetFamilyWithAProductSelectionFieldAndChannel(string $assetFamilyIdentifier, string $productField)
    {
        $productLinkRule = [
            'product_selections' => [
                [
                    'field' => $productField,
                    'operator' => '=',
                    'value' => '123456789',
                    'channel' => 'ecommerce'
                ]
            ],
            'assign_assets_to' => [
                [
                    'mode' => 'replace',
                    'attribute' => 'asset_collection'
                ]
            ]
        ];
        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily(self::ASSET_FAMILY_IDENTIFIER);
        $command = new EditAssetFamilyCommand($assetFamilyIdentifier, [], null, $assetFamilyIdentifier, [$productLinkRule], [], null);
        $this->editAssetFamily($command);
    }

    /**
     * @When /^the user updates an asset family "([^"]*)" with a product selection field "([^"]*)" and locale$/
     */
    public function theUserUpdatesAnAssetFamilyWithAProductSelectionFieldAndLocale(string $assetFamilyIdentifier, string $productField)
    {
        $productLinkRule = [
            'product_selections' => [
                [
                    'field' => $productField,
                    'operator' => '=',
                    'value' => '123456789',
                    'locale' => 'fr_FR',
                ]
            ],
            'assign_assets_to' => [
                [
                    'mode' => 'replace',
                    'attribute' => 'asset_collection'
                ]
            ]
        ];
        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily(self::ASSET_FAMILY_IDENTIFIER);
        $command = new EditAssetFamilyCommand($assetFamilyIdentifier, [], null, $attributeAsMainMedia, [$productLinkRule], [], null);
        $this->editAssetFamily($command);
    }

    /**
     * @When /^the user updates this asset family with a product link rule having an assignment attribute which references a product attribute which type does not point to the asset we are trying to update$/
     */
    public function theUserUpdatesThisAssetFamilyWithAProductLinkRuleHavingAnAssignmentAttributeWhichReferencesAProductAttributeWhichTypeDoesNotPointToTheAssetWeAreTryingToUpdate()
    {
        $this->inMemoryFindAssetCollectionTypeACL->stubWith('WRONG_ATTRIBUTE_TYPE');
        $productLinkRule = [
            'product_selections' => [
                [
                    'field' => 'sku',
                    'operator' => '=',
                    'value' => '123456789',
                ]
            ],
            'assign_assets_to' => [
                [
                    'mode' => 'replace',
                    'attribute' => self::ATTRIBUTE_CODE,
                ]
            ]
        ];
        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily(self::ASSET_FAMILY_IDENTIFIER);
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, $attributeAsMainMedia, [$productLinkRule], [], null);
        $this->editAssetFamily($command);
    }

    /**
     * @When /^the user updates this asset family with a product link rule having an assignment attribute which references a product attribute which does not exist$/
     */
    public function theUserUpdatesThisAssetFamilyWithAProductLinkRuleHavingAnAssignmentAttributeWhichReferencesAProductAttributeWhichTypeDoesNotExist()
    {
        $attributeDoesNotExistException = new ProductAttributeDoesNotExistException(
            'Expected product attribute to exist, none found'
        );
        $this->inMemoryFindAssetCollectionTypeACL->stubWithException($attributeDoesNotExistException);

        $productLinkRule = [
            'product_selections' => [
                [
                    'field' => 'sku',
                    'operator' => '=',
                    'value' => '123456789',
                ]
            ],
            'assign_assets_to' => [
                [
                    'mode' => 'replace',
                    'attribute' => self::ATTRIBUTE_CODE,
                ]
            ]
        ];
        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily(self::ASSET_FAMILY_IDENTIFIER);
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, $attributeAsMainMedia, [$productLinkRule], [], null);
        $this->editAssetFamily($command);
    }

    /**
     * @Then /^there should be a validation error stating that this attribute has not the same type of the asset family we are trying to update$/
     */
    public function thereShouldBeAValidationErrorStatingThatThisAttributeHasNotTheSameOfTheReferenceEntityWeAreTryingToUpdate()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            sprintf(
                'The product attribute "%s" cannot contain assets of asset family "%s"',
                self::ATTRIBUTE_CODE,
                self::ASSET_FAMILY_IDENTIFIER
            )
        );
    }

    private function editAssetFamily(EditAssetFamilyCommand $editAssetFamilyCommand): void
    {
        $this->constraintViolationsContext->addViolations($this->validator->validate($editAssetFamilyCommand));

        if (!$this->constraintViolationsContext->hasViolations()) {
            ($this->editAssetFamilyHandler)($editAssetFamilyCommand);
        }
    }

    private function toExtrapolation(string $attributeCode): string
    {
        return sprintf('{{%s}}', $attributeCode);
    }

    private function createEcommerceChannel(): void
    {
        $this->channelExists->save(ChannelIdentifier::fromCode('ecommerce'));
    }

    private function createEnUsLocale(): void
    {
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
    }

    /**
     * @Given /^an asset family with a media file attribute "([^"]*)"$/
     */
    public function theAssetFamilyWithAMediaFileAttribute($mediaFileAttributeCode)
    {
        $this->fixturesLoader
            ->assetFamily(self::ASSET_FAMILY_IDENTIFIER)
            ->withAttributeOfTypeMediaFile(self::ASSET_FAMILY_IDENTIFIER, $mediaFileAttributeCode)
            ->load();
    }

    /**
     * @When /^the user updates the attribute as main media to be "([^"]*)"$/
     */
    public function theUserUpdatesTheAttributeAsMainMediaToBe($attributeAsMainMedia)
    {
        $editCommand = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, $attributeAsMainMedia, [], [], null);

        $this->editAssetFamily($editCommand);
    }

    /**
     * @Then /^the attribute as main media should be "([^"]*)"$/
     */
    public function theAttributeAsMainMediaShouldBe($attributeAsMainMedia)
    {
        $assetFamily = $this->assetFamilyRepository->getByIdentifier(AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER));
        $attributeAsMainMediaIdentifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
            $assetFamily->getIdentifier(),
            AttributeCode::fromString($attributeAsMainMedia)
        );

        Assert::assertSame($assetFamily->getAttributeAsMainMediaReference()->normalize(), $attributeAsMainMediaIdentifier->normalize());
    }

    private function getAttributeAsMainMediaCodeForFamily(string $assetFamilyIdentifier): string
    {
        $attributeIdentifier = $this->assetFamilyRepository->getByIdentifier(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier)
        )->getAttributeAsMainMediaReference()->getIdentifier();

        return (string) $this->attributeRepository->getByIdentifier($attributeIdentifier)->getCode();
    }

    /**
     * @Then /^there should be a validation error stating that this product attribute does not exist$/
     */
    public function thereShouldBeAValidationErrorStatingThatThisProductAttributeDoesNotExist()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            sprintf(
                'product attribute "%s" does not exist',
                self::ATTRIBUTE_CODE
            )
        );
    }

    /**
     * @When /^the user updates this asset family with a product link rule having an assignment attribute which references a product attribute which cannot contain an asset$/
     */
    public function theUserUpdatesThisAssetFamilyWithAProductLinkRuleHavingAnAssignmentAttributeWhichReferencesAProductAttributeWhichCannotContainAnAsset()
    {
        $attributeDoesNotExistException = new ProductAttributeCannotContainAssetsException();
        $this->inMemoryFindAssetCollectionTypeACL->stubWithException($attributeDoesNotExistException);

        $productLinkRule = [
            'product_selections' => [
                [
                    'field' => 'sku',
                    'operator' => '=',
                    'value' => '123456789',
                ]
            ],
            'assign_assets_to' => [
                [
                    'mode' => 'replace',
                    'attribute' => self::ATTRIBUTE_CODE,
                ]
            ]
        ];
        $attributeAsMainMedia = $this->getAttributeAsMainMediaCodeForFamily(self::ASSET_FAMILY_IDENTIFIER);
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, $attributeAsMainMedia, [$productLinkRule], [], null);
        $this->editAssetFamily($command);
    }

    /**
     * @Then /^there should be a validation error stating that this product attribute cannot contain assets$/
     */
    public function thereShouldBeAValidationErrorStatingThatThisProductAttributeCannotContainAssets()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            sprintf(
                'product attribute "%s" is not an attribute of type asset collection',
                self::ATTRIBUTE_CODE
            )
        );
    }
}
