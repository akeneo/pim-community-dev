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

    public function __construct(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        CreateAssetFamilyHandler $createAssetFamilyHandler,
        ValidatorInterface $validator,
        ExceptionContext $exceptionContext,
        ConstraintViolationsContext $violationsContext,
        InMemoryFindActivatedLocalesByIdentifiers $activatedLocales,
        int $ruleTemplateByAssetFamilyLimit
    ) {
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->createAssetFamilyHandler = $createAssetFamilyHandler;
        $this->validator = $validator;
        $this->exceptionContext = $exceptionContext;
        $this->violationsContext = $violationsContext;
        $this->activatedLocales = $activatedLocales;
        $this->ruleTemplateByAssetFamilyLimit = $ruleTemplateByAssetFamilyLimit;
    }

    /**
     * @When /^the user creates an asset family "([^"]+)" with:$/
     */
    public function theUserCreatesAnAssetFamilyWith($code, TableNode $updateTable)
    {
        $updates = current($updateTable->getHash());
        $command = new CreateAssetFamilyCommand(
            $code,
            json_decode($updates['labels'], true),
            []
        );

        $this->violationsContext->addViolations($this->validator->validate($command));

        try {
            ($this->createAssetFamilyHandler)($command);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
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

            $violations = $this->validator->validate($command);
            if ($violations->count() > 0) {
                $errorMessage = $violations->get(0)->getMessage();
                throw new \RuntimeException(
                    sprintf('Cannot create the asset family, command not valid (%s)', $errorMessage)
                );
            }

            ($this->createAssetFamilyHandler)($command);
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

        $this->violationsContext->addViolations($this->validator->validate($command));

        try {
            ($this->createAssetFamilyHandler)($command);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
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

        $this->violationsContext->addViolations($this->validator->validate($command));

        try {
            ($this->createAssetFamilyHandler)($command);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
    }

    /**
     * @return array
     */
    private function getRuleTemplate(): array
    {
        return [
            'conditions' => [
                [
                    'field'    => 'sku',
                    'operator' => '=',
                    'value'    => '{{product_sku}}'
                ]
            ],
            'actions'    => [
                [
                    'type'  => 'add',
                    'field' => '{{attribute}}',
                    'value' => '{{code}}'
                ]
            ]
        ];
    }
}
