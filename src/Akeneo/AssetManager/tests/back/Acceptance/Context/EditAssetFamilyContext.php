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
use Akeneo\AssetManager\Common\Fake\InMemoryFindActivatedLocalesByIdentifiers;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
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

    /** @var AssetFamilyRepositoryInterface */
    private $assetFamilyRepository;

    /** @var EditAssetFamilyHandler */
    private $editAssetFamilyHandler;

    /** @var CreateAssetFamilyHandler */
    private $createAssetFamilyHandler;

    /** @var ValidatorInterface */
    private $validator;

    /** @var ConstraintViolationListInterface */
    private $violations;

    /** @var ConstraintViolationsContext */
    private $constraintViolationsContext;

    /** @var InMemoryFindActivatedLocalesByIdentifiers */
    private $activatedLocales;

    /** @var int */
    private $ruleTemplateByAssetFamilyLimit;

    /** @var RuleEngineValidatorACLStub */
    private $ruleEngineValidatorACLStub;

    public function __construct(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        EditAssetFamilyHandler $editAssetFamilyHandler,
        CreateAssetFamilyHandler $createAssetFamilyHandler,
        ValidatorInterface $validator,
        ConstraintViolationsContext $constraintViolationsContext,
        InMemoryFindActivatedLocalesByIdentifiers $activatedLocales,
        RuleEngineValidatorACLStub $ruleEngineValidatorACLStub,
        int $ruleTemplateByAssetFamilyLimit
    ) {
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->editAssetFamilyHandler = $editAssetFamilyHandler;
        $this->createAssetFamilyHandler = $createAssetFamilyHandler;
        $this->validator = $validator;
        $this->constraintViolationsContext = $constraintViolationsContext;
        $this->activatedLocales = $activatedLocales;
        $this->ruleTemplateByAssetFamilyLimit = $ruleTemplateByAssetFamilyLimit;
        $this->ruleEngineValidatorACLStub = $ruleEngineValidatorACLStub;
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

    private function editAssetFamily(EditAssetFamilyCommand $editAssetFamilyCommand): void
    {
        $this->constraintViolationsContext->addViolations($this->validator->validate($editAssetFamilyCommand));

        if (!$this->constraintViolationsContext->hasViolations()) {
            ($this->editAssetFamilyHandler)($editAssetFamilyCommand);
        }
    }

    /**
     * @Given /^an empty rule template collection on the asset family \'([^\']*)\'$/
     */
    public function anEmptyRuleTemplateCollectionOnTheAssetFamily(string $code)
    {
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
                    'value'     => '{{product_sku}}'
                ]
            ],
            'assign_assets_to'    => [
                [
                    'mode'  => 'replace',
                    'attribute' => '{{attribute}}'
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
        $invalidProductLinkRules = [['product_selections' => [['field' => 'family', 'operator' => 'IN', 'camcorders']], 'assign_assets_to' => [['mode' => 'set', 'attribute' => 'collection']]]];
        $editAssetFamilyCommand = new EditAssetFamilyCommand(
            'asset_family',
            [],
            null,
            $invalidProductLinkRules
        );
        $this->editAssetFamily($editAssetFamilyCommand);
    }

    /**
     * @When /^the user updates an asset family "([^"]*)" with no product selections$/
     */
    public function theUserUpdatesAnAssetFamilyWithNoProductSelections(string $assetFamilyCode): void
    {
        $noProductSelection = [['product_selections' => [], 'assign_assets_to' => [['mode' => 'set', 'attribute' => 'collection']]]];
        $editAssetFamilyCommand = new EditAssetFamilyCommand($assetFamilyCode, [], null, $noProductSelection);
        $this->editAssetFamily($editAssetFamilyCommand);
    }

    /**
     * @When /^the user updates an asset family "([^"]*)" with no product assignment$/
     */
    public function theUserUpdatesAnAssetFamilyWithNoProductAssignment(string $assetFamilyCode): void
    {
        $noProductAssignment = [['product_selections' => [['field' => 'family', 'operator' => 'IN', 'camcorders']], 'assign_assets_to' => []]];
        $editAssetFamilyCommand = new EditAssetFamilyCommand($assetFamilyCode, [], null, $noProductAssignment);
        $this->editAssetFamily($editAssetFamilyCommand);
    }
}
