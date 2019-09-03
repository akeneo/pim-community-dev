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
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Symfony\Command\Installer\FixturesLoader;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;
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

    /** @var AssetFamilyRepositoryInterface */
    private $assetFamilyRepository;

    /** @var EditAssetFamilyHandler */
    private $editAssetFamilyHandler;

    /** @var CreateAssetFamilyHandler */
    private $createAssetFamilyHandler;

    /** @var ValidatorInterface */
    private $validator;

    /** @var ConstraintViolationsContext */
    private $constraintViolationsContext;

    /** @var InMemoryFindActivatedLocalesByIdentifiers */
    private $activatedLocales;

    /** @var int */
    private $ruleTemplateByAssetFamilyLimit;

    /** @var RuleEngineValidatorACLStub */
    private $ruleEngineValidatorACLStub;

    /** @var FixturesLoader */
    private $fixturesLoader;

    /** @var InMemoryChannelExists */
    private $channelExists;

    public function __construct(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        EditAssetFamilyHandler $editAssetFamilyHandler,
        CreateAssetFamilyHandler $createAssetFamilyHandler,
        ValidatorInterface $validator,
        ConstraintViolationsContext $constraintViolationsContext,
        InMemoryFindActivatedLocalesByIdentifiers $activatedLocales,
        RuleEngineValidatorACLStub $ruleEngineValidatorACLStub,
        FixturesLoader $fixturesLoader,
        InMemoryChannelExists $channelExists,
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
        $this->ruleTemplateByAssetFamilyLimit = $ruleTemplateByAssetFamilyLimit;
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
        $command = new EditAssetFamilyCommand(
            $identifier,
            json_decode($updates['labels'], true),
            null,
            []
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

        if (key_exists('attribute_as_label', $expectedInformation)) {
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

        if (key_exists('attribute_as_image', $expectedInformation)) {
            $expectedAttributeIdentifier = sprintf('%s_%s_%s',
                $expectedInformation['attribute_as_image'],
                $actualAssetFamily->getIdentifier(),
                md5(sprintf('%s_%s', $actualAssetFamily->getIdentifier(), $expectedInformation['attribute_as_image']))
            );

            Assert::assertTrue(
                $actualAssetFamily->getAttributeAsImageReference()->getIdentifier()->equals(
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

        $createCommand = new CreateAssetFamilyCommand($identifier, [$localCode => $label], []);

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
        $createCommand = new CreateAssetFamilyCommand($identifier, [], []);

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
            []
        );
        $this->editAssetFamily($editAssetFamilyCommand);
    }

    /**
     * @When /^the user updates the asset family \'([^\']*)\' with the label \'([^\']*)\' equal to \'([^\']*)\'$/
     */
    public function theUserUpdatesTheAssetFamilyWithTheLabelEqualTo(string $identifier, string $localCode, string $label)
    {
        $label = json_decode($label);

        $editAssetFamilyCommand = new EditAssetFamilyCommand(
            $identifier,
            [$localCode => $label],
            null,
            []
        );
        $this->editAssetFamily($editAssetFamilyCommand);
    }

    /**
     * @When /^the user updates the asset family \'([^\']*)\' with an empty image$/
     */
    public function theUserUpdatesTheAssetFamilyWithAnEmptyImage(string $identifier)
    {
        $editAssetFamilyCommand = new EditAssetFamilyCommand($identifier, [], null, []);
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
        $createCommand = new CreateAssetFamilyCommand($code, [], []);
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
        $editAssetFamilyCommand = new EditAssetFamilyCommand($code, [], null, [$ruleTemplate]);
        $this->editAssetFamily($editAssetFamilyCommand);
    }

    /**
     * @When /^the user updates the asset family \'([^\']*)\' to set a collection of rule templates having more items than the limit$/
     */
    public function theUserUpdatesTheAssetFamilyToSetACollectionOfRuleTemplatesHavingMoreItemsThanTheLimit(string $code)
    {
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));

        $ruleTemplates = [];
        for ($i = 1; $i <= $this->ruleTemplateByAssetFamilyLimit+1; $i++) {
            $ruleTemplates[] = $this->getRuleTemplate();
        }

        $editAssetFamilyCommand = new EditAssetFamilyCommand($code, ['en_US' => ucfirst($code)], null, $ruleTemplates);
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
                    'operator'  => '=',
                    'value'     => '11121313'
                ]
            ],
            'assign_assets_to'    => [
                [
                    'mode'  => 'replace',
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
            self::ASSET_FAMILY_IDENTIFIER,
            [],
            null,
            $invalidProductLinkRules
        );
        $this->editAssetFamily($editAssetFamilyCommand);
    }

    /**
     * @When /^the user updates an asset family "([^"]*)" with an empty product selections$/
     */
    public function theUserUpdatesAnAssetFamilyWithNoProductSelections(string $assetFamilyCode): void
    {
        $noProductSelection = [['product_selections' => [], 'assign_assets_to' => [['mode' => 'set', 'attribute' => 'collection']]]];
        $editAssetFamilyCommand = new EditAssetFamilyCommand($assetFamilyCode, [], null, $noProductSelection);
        $this->editAssetFamily($editAssetFamilyCommand);
    }

    /**
     * @When /^the user updates an asset family "([^"]*)" with an empty product assignment$/
     */
    public function theUserUpdatesAnAssetFamilyWithNoProductAssignment(string $assetFamilyCode): void
    {
        $noProductAssignment = [['product_selections' => [['field' => 'family', 'operator' => 'IN', 'value' => 'camcorders']], 'assign_assets_to' => []]];
        $editAssetFamilyCommand = new EditAssetFamilyCommand($assetFamilyCode, [], null, $noProductAssignment);
        $this->editAssetFamily($editAssetFamilyCommand);
    }

    /**
     * @Given /^an asset family with no product link rules and a text attribute$/
     * @Given /^an asset family with no product link rules$/
     * @Given /^an asset family with no product link rules and a channel$/
     */
    public function anAssetFamilyWithSomeAttributes()
    {
        $this->createEcommerceChannel();
        $this->fixturesLoader
            ->assetFamily(self::ASSET_FAMILY_IDENTIFIER)
            ->withAttributeOfTypeText(self::ASSET_FAMILY_IDENTIFIER, self::ATTRIBUTE_CODE)
            ->load();
    }

    /**
     * @When /^the user updates this asset family with a dynamic product link rule having a product selection field which references this text attribute$/
     */
    public function theUserCreatesAnAssetFamilyWithADynamicProductLinkRuleWhichReferencesThoseAttributes()
    {
        $dynamicRuleTemplate = [
            'product_selections' => [
                [
                    'field' => $this->toExtrapolation(self::ATTRIBUTE_CODE),
                    'operator'  => '=',
                    'value'     => '123456789',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ],
            'assign_assets_to'    => [
                [
                    'mode'      => 'replace',
                    'attribute' => 'asset_collection',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ]
        ];
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, [$dynamicRuleTemplate]);
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
                    'operator'  => '=',
                    'value'     => $this->toExtrapolation(self::ATTRIBUTE_CODE),
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ],
            'assign_assets_to'    => [
                [
                    'mode'      => 'replace',
                    'attribute' => 'asset_collection',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ]
        ];
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, [$dynamicRuleTemplate]);
        $this->editAssetFamily($command);
    }

    /**
     * @When /^the user updates this asset family with a dynamic product link rule having a dynamic assignment value which references this text attribute$/
     */
    public function theUserUpdatesThisAssetFamilyWithADynamicProductLinkRuleHavingADynamicAssignmentAttributeValueWhichReferencesThisAttribute(
    ) {
        $dynamicRuleTemplate = [
            'product_selections' => [
                [
                    'field' => 'sku',
                    'operator'  => '=',
                    'value'     => '123456789',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ],
            'assign_assets_to'    => [
                [
                    'mode'      => 'replace',
                    'attribute' => $this->toExtrapolation(self::ATTRIBUTE_CODE),
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ]
        ];
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, [$dynamicRuleTemplate]);
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
                    'operator'  => '=',
                    'value'     => $this->toExtrapolation(self::ATTRIBUTE_CODE),
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ],
            'assign_assets_to'    => [
                [
                    'mode'      => 'replace',
                    'attribute' => 'asset_collection',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ]
        ];
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, [$dynamicRuleTemplate]);
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
                    'operator'  => '=',
                    'value'     => $this->toExtrapolation(self::ATTRIBUTE_CODE),
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ],
            'assign_assets_to'    => [
                [
                    'mode'      => 'replace',
                    'attribute' => 'asset_collection',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ]
        ];
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, [$dynamicRuleTemplate]);
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
                    'operator'  => '=',
                    'value'     => '11234567899',
                    'channel' => $this->toExtrapolation(self::ATTRIBUTE_CODE),
                    'locale' => 'en_US',
                ]
            ],
            'assign_assets_to'    => [
                [
                    'mode'      => 'replace',
                    'attribute' => 'asset_collection',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ]
        ];
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, [$dynamicRuleTemplate]);
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
                    'operator'  => '=',
                    'value'     => '11234567899',
                    'channel' => 'ecommerce',
                    'locale' => $this->toExtrapolation(self::ATTRIBUTE_CODE),
                ]
            ],
            'assign_assets_to'    => [
                [
                    'mode'      => 'replace',
                    'attribute' => 'asset_collection',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ]
        ];
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, [$dynamicRuleTemplate]);
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
                    'operator'  => '=',
                    'value'     => '11234567899',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ],
            'assign_assets_to'    => [
                [
                    'mode'      => 'replace',
                    'attribute' => 'asset_collection',
                    'channel' => $this->toExtrapolation(self::ATTRIBUTE_CODE),
                    'locale' => 'en_US',
                ]
            ]
        ];
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, [$dynamicRuleTemplate]);
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
                    'operator'  => '=',
                    'value'     => '11234567899',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ],
            'assign_assets_to'    => [
                [
                    'mode'      => 'replace',
                    'attribute' => 'asset_collection',
                    'channel' => 'ecommerce',
                    'locale' => $this->toExtrapolation(self::ATTRIBUTE_CODE),
                ]
            ]
        ];
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, [$dynamicRuleTemplate]);
        $this->editAssetFamily($command);
    }

    /**
     * @Given /^an asset family with no product link rules and an attribute with a type unsupported for extrapolation$/
     */
    public function anAssetFamilyWithNoProductLinkRulesAndAnImageAttribute()
    {
        $this->fixturesLoader
            ->assetFamily(self::ASSET_FAMILY_IDENTIFIER)
            ->withAttributeOfTypeImage(self::ASSET_FAMILY_IDENTIFIER, self::ATTRIBUTE_CODE)
            ->load();
    }

    /**
     * @When /^the user updates this asset family with a dynamic product link rule having a product selection field which references this attribute$/
     */
    public function theUserUpdatesThisAssetFamilyWithADynamicProductLinkRuleHavingAProductSelectionFieldWhichReferencesAnAttributeHavingAnUnsupportedAttributeType()
    {
        $dynamicRuleTemplate = [
            'product_selections' => [
                [
                    'field' => $this->toExtrapolation(self::ATTRIBUTE_CODE),
                    'operator'  => '=',
                    'value'     => '123456789',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ],
            'assign_assets_to'    => [
                [
                    'mode'      => 'replace',
                    'attribute' => 'asset_collection',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ]
        ];
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, [$dynamicRuleTemplate]);
        $this->editAssetFamily($command);
    }

    /**
     * @Then /^there should be a validation error stating that the product selection field does not support this attribute for extrapolation$/
     */
    public function thereShouldBeAValidationErrorStatingThatTheProductSelectionFieldDoesNotSupportExtrapolatedImageAttribute()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            sprintf('The attribute "%s" of type "image" is not supported, only the following attribute types are supported for this field: text', self::ATTRIBUTE_CODE)
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
                    'operator'  => '=',
                    'value'     => $this->toExtrapolation(self::ATTRIBUTE_CODE),
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ],
            'assign_assets_to'    => [
                [
                    'mode'      => 'replace',
                    'attribute' => 'asset_collection',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ]
        ];
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, [$dynamicRuleTemplate]);
        $this->editAssetFamily($command);
    }

    /**
     * @When /^there should be a validation error stating that the product selection value does not support this attribute for extrapolation/
     */
    public function there_should_be_a_validation_error_stating_that_the_product_selection_value_does_not_support_extrapolated_image_attribute()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            sprintf('The attribute "%s" of type "image" is not supported, only the following attribute types are supported for this field: text, option, option_collection', self::ATTRIBUTE_CODE)
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
                    'operator'  => '=',
                    'value'     => '123444456789',
                    'channel' => $this->toExtrapolation(self::ATTRIBUTE_CODE),
                    'locale' => 'en_US',
                ]
            ],
            'assign_assets_to'    => [
                [
                    'mode'      => 'replace',
                    'attribute' => 'asset_collection',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ]
        ];
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, [$dynamicRuleTemplate]);
        $this->editAssetFamily($command);
    }

    /**
     * @Then /^there should be a validation error stating that the product selection channel does not support this attribute for extrapolation$/
     */
    public function thereShouldBeAValidationErrorStatingThatTheProductSelectionChannelDoesNotSupportExtrapolatedImageAttribute()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            sprintf('The attribute "%s" of type "image" is not supported, only the following attribute types are supported for this field: text', self::ATTRIBUTE_CODE)
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
                    'operator'  => '=',
                    'value'     => '123444456789',
                    'channel' => 'ecommerce',
                    'locale' => $this->toExtrapolation(self::ATTRIBUTE_CODE),
                ]
            ],
            'assign_assets_to'    => [
                [
                    'mode'      => 'replace',
                    'attribute' => 'asset_collection',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ]
        ];
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, [$dynamicRuleTemplate]);
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
                    'operator'  => '=',
                    'value'     => '123444456789',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ],
            'assign_assets_to'    => [
                [
                    'mode'      => 'replace',
                    'attribute' => $this->toExtrapolation(self::ATTRIBUTE_CODE),
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ]
        ];
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, [$dynamicRuleTemplate]);
        $this->editAssetFamily($command);
    }

    /**
     * @Then /^there should be a validation error stating that the product selection locale does not support this attribute for extrapolation$/
     */
    public function thereShouldBeAValidationErrorStatingThatTheAssignmentAttributeDoesNotSupportThisExtrapolatedAttributeType()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            sprintf('The attribute "%s" of type "image" is not supported, only the following attribute types are supported for this field: text', self::ATTRIBUTE_CODE)
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
                    'operator'  => '=',
                    'value'     => '123444456789',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ],
            'assign_assets_to'    => [
                [
                    'mode'      => 'replace',
                    'attribute' => 'my_asset_collection',
                    'channel' => $this->toExtrapolation(self::ATTRIBUTE_CODE),
                    'locale' => 'en_US',
                ]
            ]
        ];
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, [$dynamicRuleTemplate]);
        $this->editAssetFamily($command);
    }

    /**
     * @Then /^there should be a validation error stating that the product assignment channel does not support this attribute for extrapolation$/
     */
    public function thereShouldBeAValidationErrorStatingThatTheProductAssignmentChannelDoesNotSupportThisAttributeForExtrapolation()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            sprintf('The attribute "%s" of type "image" is not supported, only the following attribute types are supported for this field: text', self::ATTRIBUTE_CODE)
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
                    'operator'  => '=',
                    'value'     => '123444456789',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ],
            'assign_assets_to'    => [
                [
                    'mode'      => 'replace',
                    'attribute' => 'my_asset_collection',
                    'channel' => 'ecommerce',
                    'locale' => $this->toExtrapolation(self::ATTRIBUTE_CODE),
                ]
            ]
        ];
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, [$dynamicRuleTemplate]);
        $this->editAssetFamily($command);
    }

    /**
     * @Then /^there should be a validation error stating that the product assignment locale does not support this attribute for extrapolation$/
     */
    public function thereShouldBeAValidationErrorStatingThatTheProductAssignmentLocaleDoesNotSupportThisAttributeForExtrapolation()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            sprintf('The attribute "%s" of type "image" is not supported, only the following attribute types are supported for this field: text', self::ATTRIBUTE_CODE)
        );
    }

    /**
     * @When /^the user updates this asset family with a product link rule having a no product selection channel$/
     */
    public function theUserUpdatesThisAssetFamilyWithAProductLinkRuleHavingANoProductSelectionChannel()
    {
        $dynamicRuleTemplate = [
            'product_selections' => [
                [
                    'field' => 'sku',
                    'operator'  => '=',
                    'value'     => '11234567899',
                    // No channel
                    'locale' => 'en_US',
                ]
            ],
            'assign_assets_to'    => [
                [
                    'mode'      => 'replace',
                    'attribute' => 'asset_collection',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ]
        ];
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, [$dynamicRuleTemplate]);
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
                    'field'    => 'sku',
                    'operator' => '=',
                    'value'    => '11234567899',
                    'channel'  => 'ecommerce',
                    'locale'   => 'en_US',
                ],
            ],
            'assign_assets_to'   => [
                [
                    'mode'      => 'replace',
                    'attribute' => 'asset_collection',
                    'channel'   => 'ecommerce',
                    'locale'    => 'en_US',
                ],
            ],
        ];
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, [$dynamicRuleTemplate]);
        $this->editAssetFamily($command);
    }

    /**
     * @Given /^a channel$/
     */
    public function aChannel()
    {
        $this->createEcommerceChannel();
    }

    /**
     * @Then /^there should be a validation error stating that the product selection channel does not exist$/
     */
    public function thereShouldBeAValidationErrorStatingThatTheProductSelectionChannelDoesNotExist()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            sprintf('The channel "%s" of does not exist', self::UNKNOWN_CHANNEL)
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
                    'field'    => 'sku',
                    'operator' => '=',
                    'value'    => '11234567899',
                    'channel'  => self::UNKNOWN_CHANNEL,
                    'locale'   => 'en_US',
                ],
            ],
            'assign_assets_to'   => [
                [
                    'mode'      => 'replace',
                    'attribute' => 'asset_collection',
                    'channel'   => 'ecommerce',
                    'locale'    => 'en_US',
                ],
            ],
        ];
        $command = new EditAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], null, [$dynamicRuleTemplate]);
        $this->editAssetFamily($command);
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
}
