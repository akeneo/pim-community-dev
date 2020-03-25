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

use Akeneo\Pim\Automation\RuleEngine\Component\Connector\Processor\Normalization\RuleDefinitionProcessor as RuleDefinitionNormalizerProcessor;
use Akeneo\Test\Pim\Automation\RuleEngine\Common\Context\ExceptionContext;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Symfony\Component\Yaml\Yaml;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class ExportRuleContext implements Context
{
    private static $normalizedRules = [];

    /** @var RuleDefinitionRepositoryInterface */
    private $ruleDefinitionRepository;

    /** @var RuleDefinitionNormalizerProcessor */
    private $ruleDefinitionNormalizerProcessor;

    public function __construct(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        RuleDefinitionNormalizerProcessor $ruleDefinitionNormalizerProcessor
    ) {
        $this->ruleDefinitionRepository = $ruleDefinitionRepository;
        $this->ruleDefinitionNormalizerProcessor = $ruleDefinitionNormalizerProcessor;
    }

    /**
     * @When /^I export all the rules$/
     */
    public function exportAllTheRules()
    {
        static::$normalizedRules = [];
        $rules = $this->ruleDefinitionRepository->findAll();

        foreach ($rules as $rule) {
            try {
                static::$normalizedRules[] = $this->ruleDefinitionNormalizerProcessor->process($rule);
            } catch (\Exception $e) {
                ExceptionContext::addException($e);
            }
        }
    }

    /**
     * @Then /^the exported yaml file should contain:$/
     */
    public function theExportFileContainsAllRules(PyStringNode $yaml): void
    {
        $expectedLines = Yaml::parse($yaml->getRaw());
        // The YAML writer does this merge before write the line. We do the same thing to compare.
        $normalizedRules = call_user_func_array('array_merge', static::$normalizedRules);

        Assert::eq($normalizedRules, $expectedLines['rules']);
    }
}
