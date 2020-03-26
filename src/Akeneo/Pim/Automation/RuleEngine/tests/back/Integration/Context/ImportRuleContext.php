<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\RuleEngine\Integration\Context;

use Akeneo\Pim\Automation\RuleEngine\Component\Connector\Processor\Denormalization\RuleDefinitionProcessor;
use Akeneo\Test\Pim\Automation\RuleEngine\Common\Context\ExceptionContext;
use Akeneo\Tool\Bundle\RuleEngineBundle\Doctrine\Common\Saver\RuleDefinitionSaver;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Symfony\Component\Yaml\Yaml;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class ImportRuleContext implements Context
{
    /** @var RuleDefinitionProcessor */
    private $ruleDefinitionProcessor;

    /** @var RuleDefinitionRepositoryInterface */
    private $ruleDefinitionRepository;

    /** @var RuleDefinitionSaver */
    private $ruleDefinitionSaver;

    public function __construct(
        RuleDefinitionProcessor $ruleDefinitionProcessor,
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        RuleDefinitionSaver $ruleDefinitionSaver
    ) {
        $this->ruleDefinitionProcessor = $ruleDefinitionProcessor;
        $this->ruleDefinitionRepository = $ruleDefinitionRepository;
        $this->ruleDefinitionSaver = $ruleDefinitionSaver;
    }

    /**
     * @Given /^the following yaml file to import:$/
     */
    public function theFollowingYamlToImport(PyStringNode $yaml): void
    {
        $this->importRules($yaml->getRaw());
    }

    /**
     * @Then /^the rule list contains the rules?:$/
     */
    public function theRuleListContainsTheValidConcatenateRule(PyStringNode $yaml)
    {
        $rules = Yaml::parse($yaml->getRaw());
        $ruleDefinitions = $this->ruleDefinitionRepository->findAll();

        foreach ($rules as $ruleCode => $content) {
            /** @var RuleDefinitionInterface $ruleDefinition */
            foreach ($ruleDefinitions as $ruleDefinition) {
                if ($ruleDefinition->getCode() === $ruleCode) {
                    Assert::same($content, $ruleDefinition->getContent());

                    continue 2;
                }
            }

            throw new \LogicException(sprintf('The "%s" rule was not found.', $ruleCode));
        }
    }

    /**
     * @Then /^the rule list does not contain the rule "([^"]*)"$/
     */
    public function theRuleListDoesNotContainTheRule(string $ruleCode)
    {
        $ruleDefinitions = $this->ruleDefinitionRepository->findAll();
        /** @var RuleDefinitionInterface $ruleDefinition */
        foreach ($ruleDefinitions as $ruleDefinition) {
            if ($ruleDefinition->getCode() === $ruleCode) {
                throw new \LogicException(sprintf('The "%s" rule is found, it should not.', $ruleCode));
            }
        }
    }

    private function importRules(string $yaml)
    {
        $normalizedRules = Yaml::parse($yaml);

        foreach ($normalizedRules['rules'] as $code => $normalizedRule) {
            $normalizedRule['code'] = $code;
            try {
                $ruleDefinition = $this->ruleDefinitionProcessor->process($normalizedRule);
                $this->ruleDefinitionSaver->save($ruleDefinition);
            } catch (\Exception $e) {
                ExceptionContext::addException($e);
            }
        }
    }
}
