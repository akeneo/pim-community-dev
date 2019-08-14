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
use Akeneo\AssetManager\Common\Fake\InMemoryAssetFamilyRepository;
use Akeneo\AssetManager\Common\Fake\InMemoryFindActivatedLocalesByIdentifiers;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Common\Fake\Anticorruption\RuleEngineValidatorACLStub;
use Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules\RuleEngineValidatorACLInterface;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
final class CreateAssetFamilyContext implements Context
{
    private const RULE_ENGINE_VALIDATION_MESSAGE = 'RULE ENGINE WILL NOT EXECUTE';

    /** @var InMemoryAssetFamilyRepository */
    private $assetFamilyRepository;

    /** @var CreateAssetFamilyHandler */
    private $createAssetFamilyHandler;

    /** @var ValidatorInterface */
    private $validator;

    /** @var ExceptionContext */
    private $exceptionContext;

    /** @var ConstraintViolationsContext */
    private $violationsContext;

    /** @var InMemoryFindActivatedLocalesByIdentifiers */
    private $activatedLocales;

    /** @var int  */
    private $ruleTemplateByAssetFamilyLimit;

    /** @var RuleEngineValidatorACLStub */
    private $ruleEngineValidatorACLStub;

    public function __construct(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        CreateAssetFamilyHandler $createAssetFamilyHandler,
        ValidatorInterface $validator,
        ExceptionContext $exceptionContext,
        ConstraintViolationsContext $violationsContext,
        InMemoryFindActivatedLocalesByIdentifiers $activatedLocales,
        RuleEngineValidatorACLInterface $ruleEngineValidatorACLStub,
        int $ruleTemplateByAssetFamilyLimit
    ) {
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->createAssetFamilyHandler = $createAssetFamilyHandler;
        $this->validator = $validator;
        $this->exceptionContext = $exceptionContext;
        $this->violationsContext = $violationsContext;
        $this->activatedLocales = $activatedLocales;
        $this->ruleTemplateByAssetFamilyLimit = $ruleTemplateByAssetFamilyLimit;
        $this->ruleEngineValidatorACLStub = $ruleEngineValidatorACLStub;
        $this->activateDefaultLocales();
    }

    /**
     * @When /^the user creates an asset family "([^"]+)" with:$/
     */
    public function theUserCreatesAnAssetFamilyWith($code, TableNode $updateTable)
    {
        $updates = current($updateTable->getHash());
        $command = new CreateAssetFamilyCommand(
            $code,
            json_decode($updates['labels'] ?? '[]', true),
            json_decode($updates['product_link_rules'] ?? '[]', true)
        );

        $this->createAssetFamily($command);
    }

    /**
     * @Then /^there is an asset family "([^"]+)" with:$/
     */
    public function thereIsAnAssetFamilyWith(string $code, TableNode $assetFamilyTable)
    {
        $expectedIdentifier = AssetFamilyIdentifier::fromString($code);
        $expectedInformation = current($assetFamilyTable->getHash());
        $actualAssetFamily = $this->assetFamilyRepository->getByIdentifier($expectedIdentifier);
        $this->assertSameLabels(
            json_decode($expectedInformation['labels'], true),
            $actualAssetFamily
        );
        $this->assertSameProductLinkRules(json_decode($expectedInformation['product_link_rules'] ?? '[]', true), $actualAssetFamily);
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
     * @Given /^there should be no asset family$/
     */
    public function thereShouldBeNoAssetFamily()
    {
        $assetFamilyCount = $this->assetFamilyRepository->count();
        Assert::assertSame(
            0,
            $assetFamilyCount,
            sprintf('Expected to have 0 asset family. %d found.', $assetFamilyCount)
        );
    }

    /**
     * @Given /^(\d+) random asset families$/
     */
    public function randomAssetFamilies(int $number)
    {
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));

        for ($i = 0; $i < $number; $i++) {
            $command = new CreateAssetFamilyCommand(
                uniqid(),
                ['en_US' => uniqid('label_')],
                []
            );
            $this->createAssetFamily($command);
        }
    }

    /**
     * @When /^the user creates an asset family '([^"]*)' with a collection of rule templates$/
     */
    public function theUserCreatesAnAssetFamilyWithACollectionOfRuleTemplates(string $code): void
    {
        $ruleTemplate = $this->getRuleTemplate();

        $command = new CreateAssetFamilyCommand(
            $code,
            [],
            [$ruleTemplate]
        );

        $this->createAssetFamily($command);
    }

    /**
     * @Then /^there is an asset family '([^"]*)' with a collection of rule templates$/
     */
    public function thereIsAnAssetFamilyWithACollectionOfRuleTemplates(string $code): void
    {
        $expectedIdentifier = AssetFamilyIdentifier::fromString($code);
        $actualAssetFamily = $this->assetFamilyRepository->getByIdentifier($expectedIdentifier);
        $expectedRuleTemplate = $this->getRuleTemplate();
        $expectedRuleTemplateCollection = RuleTemplateCollection::createFromProductLinkRules([$expectedRuleTemplate]);

        Assert::assertEquals($expectedRuleTemplateCollection, $actualAssetFamily->getRuleTemplateCollection());
    }

    /**
     * @When /^the user tries to create an asset family \'([^\']*)\' with a collection of rule templates having more items than the limit$/
     */
    public function theUserTriesToCreateAnAssetFamilyWithACollectionOfRuleTemplatesHavingMoreItemsThanTheLimit(string $code)
    {
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));

        $ruleTemplates = [];
        for ($i = 1; $i <= $this->ruleTemplateByAssetFamilyLimit+1; $i++) {
            $ruleTemplates[] = $this->getRuleTemplate();
        }

        $command = new CreateAssetFamilyCommand(
            $code,
            ['en_US' => ucfirst($code)],
            $ruleTemplates
        );

        $this->createAssetFamily($command);
    }

    /**
     * @When /^the user creates an asset family "([^"]*)" with a product link rule not executable by the rule engine$/
     */
    public function theUserCreatesAnAssetFamilyWithAProductLinkRuleNotExecutableByTheRuleEngine(string $assetFamilyCode): void
    {
        $this->ruleEngineValidatorACLStub->stubWithViolationMessage(self::RULE_ENGINE_VALIDATION_MESSAGE);
        $invalidProductLinkRules = [['product_selections' => [['field' => 'family', 'operator' => 'IN', 'camcorders']], 'assign_assets_to' => [['mode' => 'set', 'attribute' => 'collection']]]];
        $createAssetFamilyWithInvalidProductLinkRulesCommand = new CreateAssetFamilyCommand(
            'asset_family',
            [],
            $invalidProductLinkRules
        );
        $this->createAssetFamily($createAssetFamilyWithInvalidProductLinkRulesCommand);
    }

    /**
     * @Then /^there should be a validation error stating why the rule engine cannot execute the product link rule$/
     */
    public function thereShouldBeAValidationErrorStatingWhyTheRuleEngineCannotExecuteTheProductLinkRule()
    {
        $this->violationsContext->thereShouldBeAValidationErrorWithMessage(self::RULE_ENGINE_VALIDATION_MESSAGE);
    }

    /**
     * @return array
     */
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
                    'mode'      => 'replace',
                    'attribute' => '{{attribute}}'
                ]
            ]
        ];
    }

    private function activateDefaultLocales(): void
    {
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));
    }

    private function assertSameProductLinkRules(array $expectedNormalizedProductLinkRules, AssetFamily $actualAssetFamily): void
    {
        $actualProductLinks = $actualAssetFamily->getRuleTemplateCollection()->normalize();
        $expectedNormalizedProductLinkRules = [
            [
                'conditions' => [
                    [
                        'field'    => $expectedNormalizedProductLinkRules[0]['product_selections'][0]['field'],
                        'operator' => $expectedNormalizedProductLinkRules[0]['product_selections'][0]['operator'],
                        'value'    => $expectedNormalizedProductLinkRules[0]['product_selections'][0]['value'],
                        'channel'  => $expectedNormalizedProductLinkRules[0]['product_selections'][0]['channel'],
                        'locale'   => $expectedNormalizedProductLinkRules[0]['product_selections'][0]['locale'],
                    ],
                ],
                'actions'    => [
                    [
                        'type'    => $expectedNormalizedProductLinkRules[0]['assign_assets_to'][0]['mode'],
                        'field'   => $expectedNormalizedProductLinkRules[0]['assign_assets_to'][0]['attribute'],
                        'channel' => null,
                        'locale'  => null,
                        'items'   => ['{{code}}'],
                    ],
                ],
            ],
        ];

        Assert::assertEquals($expectedNormalizedProductLinkRules, $actualProductLinks);
    }

    private function createAssetFamily(CreateAssetFamilyCommand $command): void
    {
        $violationList = $this->validator->validate($command);
        if (0 !== $violationList->count()) {
            $this->violationsContext->addViolations($violationList);

            return;
        }

        try {
            ($this->createAssetFamilyHandler)($command);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
    }
}
