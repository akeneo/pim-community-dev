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
use Akeneo\Pim\Automation\RuleEngine\Component\Connector\Processor\Normalization\RuleDefinitionProcessor as NormalizationRuleDefinitionProcessor;
use Akeneo\Test\Pim\Automation\RuleEngine\Common\Context\ExceptionContext;
use Akeneo\Tool\Bundle\RuleEngineBundle\Doctrine\Common\Saver\RuleDefinitionSaver;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
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
    /** @var null|array */
    private static $importedRules = null;

    /** @var RuleDefinitionProcessor */
    private $ruleDefinitionProcessor;

    /** @var RuleDefinitionRepositoryInterface */
    private $ruleDefinitionRepository;

    /** @var RuleDefinitionSaver */
    private $ruleDefinitionSaver;

    /** @var NormalizationRuleDefinitionProcessor */
    private $normalizationRuleDefinitionProcessor;

    /** @var EntityManagerClearerInterface */
    private $entityManagerClearer;

    /** @var string */
    private $kernelRootDir;

    public function __construct(
        RuleDefinitionProcessor $ruleDefinitionProcessor,
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        RuleDefinitionSaver $ruleDefinitionSaver,
        NormalizationRuleDefinitionProcessor $normalizationRuleDefinitionProcessor,
        EntityManagerClearerInterface $entityManagerClearer,
        string $kernelRootDir
    ) {
        $this->ruleDefinitionProcessor = $ruleDefinitionProcessor;
        $this->ruleDefinitionRepository = $ruleDefinitionRepository;
        $this->ruleDefinitionSaver = $ruleDefinitionSaver;
        $this->normalizationRuleDefinitionProcessor = $normalizationRuleDefinitionProcessor;
        $this->entityManagerClearer = $entityManagerClearer;
        $this->kernelRootDir = $kernelRootDir;
    }

    /**
     * @When /^the following yaml file is imported:$/
     */
    public function theFollowingYamlToImport(PyStringNode $yaml): void
    {
        $string = $this->replacePlaceholders($yaml->getRaw());
        static::$importedRules = Yaml::parse($string)['rules'];

        $this->importRules($string);
    }

    /**
     * @Then /^the rule list contains the rules?:$/
     */
    public function theRuleListContainsTheValidConcatenateRule(PyStringNode $yaml)
    {
        // Clear the entity manager to avoid cache issues.
        $this->entityManagerClearer->clear();

        $rules = Yaml::parse($yaml->getRaw());
        $ruleDefinitions = $this->ruleDefinitionRepository->findAll();

        foreach ($rules as $ruleCode => $content) {
            /** @var RuleDefinitionInterface $ruleDefinition */
            foreach ($ruleDefinitions as $ruleDefinition) {
                if ($ruleDefinition->getCode() === $ruleCode) {
                    $normalizedRuleDefinition = $this->normalizationRuleDefinitionProcessor->process($ruleDefinition)[$ruleCode];
                    $this->assertSameRuleContent($normalizedRuleDefinition, $content);

                    continue 2;
                }
            }

            throw new \LogicException(sprintf('The "%s" rule was not found.', $ruleCode));
        }
    }

    /**
     * @Then the rule list contains the imported rules
     */
    public function theRuleListContainsTheImportedRules()
    {
        Assert::notNull(static::$importedRules, 'No rule is imported.');
        $ruleDefinitions = $this->ruleDefinitionRepository->findAll();
        $indexedRuleDefinitions = [];
        foreach ($ruleDefinitions as $ruleDefinition) {
            $indexedRuleDefinitions[$ruleDefinition->getCode()] = $ruleDefinition;
        }

        foreach (static::$importedRules as $ruleCode => $content) {
            $ruleDefinition = $indexedRuleDefinitions[$ruleCode] ?? null;
            Assert::notNull($ruleDefinition, sprintf('The "%s" rule was not found.', $ruleCode));

            Assert::eq($ruleDefinition->getPriority(), $content['priority'] ?? 0);
            Assert::eq($ruleDefinition->isEnabled(), $content['enabled'] ?? true);
            unset($content['priority']);
            unset($content['enabled']);
            $this->assertSameRuleContent($ruleDefinition->getContent(), $content);
        }
    }

    /**
     * @Then the :ruleCode rule is enabled
     */
    public function theRuleisEnabled(string $ruleCode)
    {
        $ruleDefinition = $this->ruleDefinitionRepository->findOneByIdentifier($ruleCode);
        Assert::notNull($ruleDefinition, sprintf('The "%s" rule was not found.', $ruleCode));
        Assert::true($ruleDefinition->isEnabled(), sprintf('The "%s" rule is disabled.', $ruleCode));
    }

    /**
     * @Then the :ruleCode rule is disabled
     */
    public function theRuleisDisabled(string $ruleCode)
    {
        $ruleDefinition = $this->ruleDefinitionRepository->findOneByIdentifier($ruleCode);
        Assert::notNull($ruleDefinition, sprintf('The "%s" rule was not found.', $ruleCode));
        Assert::false($ruleDefinition->isEnabled(), sprintf('The "%s" rule is enabled.', $ruleCode));
    }

    /**
     * @Then /^the rule list does not contain the "([^"]*)" rule$/
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

    private function replacePlaceholders(string $string): string
    {
        return strtr($string, [
            '%tmp%' => !empty($_ENV['BEHAT_TMPDIR'] ?? '') ? $_ENV['BEHAT_TMPDIR'] : '/tmp/pim-behat',
            '%fixtures%' => $this->kernelRootDir . '/../tests/legacy/features/Context/fixtures/',
            '%web%' => $this->kernelRootDir . '/../public/',
        ]);
    }

    private function assertSameRuleContent(array $value, array $expected): void
    {
        // Media files are modified during the import. We have to remove them to compare.
        $value = $this->addLabelsIfNotSet($this->replaceMediaFilesInRuleContent($value));
        $expected = $this->addLabelsIfNotSet($this->replaceMediaFilesInRuleContent($expected));

        Assert::eq($value, $expected, sprintf(
            "Expecting '%s', got '%s'.",
            json_encode($expected),
            json_encode($value)
        ));
    }

    private function replaceMediaFilesInRuleContent(array $ruleContent): array
    {
        foreach ($ruleContent['actions'] ?? [] as $key => $action) {
            if (!isset($action['value']) || !is_string($action['value'])) {
                continue;
            }

            if (strpos($action['value'], '.') !== false && strpos($action['value'], '/') !== false) {
                $ruleContent['actions'][$key]['value'] = 'media.jpg';
            }
        }

        return $ruleContent;
    }

    private function addLabelsIfNotSet(array $ruleContent): array
    {
        if (!array_key_exists('labels', $ruleContent)) {
            $ruleContent['labels'] = [];
        }

        return $ruleContent;
    }
}
