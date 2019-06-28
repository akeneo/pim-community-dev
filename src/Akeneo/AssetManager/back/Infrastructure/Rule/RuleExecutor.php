<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Rule;

use Akeneo\AssetManager\Domain\Query\Asset\PropertyAccessibleAsset;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate;
use Akeneo\Tool\Bundle\RuleEngineBundle\Runner\RunnerInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinition;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Akeneo\Tool\Component\Console\CommandLauncher;

class RuleExecutor {
  const RUN_COMMAND = 'akeneo:asset-manager:run-rule \'%s\'';

  /** @var RuleCompiler */
  private $ruleCompiler;

  /** @var RunnerInterface */
  private $ruleRunner;

  /** @var NormalizerInterface */
  private $ruleNormalizer;

  /** @var NormalizerInterface */
  private $commandLauncher;

  public function __construct(
      RuleCompiler $ruleCompiler,
      RunnerInterface $ruleRunner,
      NormalizerInterface $ruleNormalizer,
      CommandLauncher $commandLauncher
  ) {
      $this->ruleCompiler = $ruleCompiler;
      $this->ruleRunner = $ruleRunner;
      $this->ruleNormalizer = $ruleNormalizer;
      $this->commandLauncher = $commandLauncher;
  }

  public function execute(RuleTemplate $ruleTemplate, PropertyAccessibleAsset $asset) {
    $rule = $this->ruleCompiler->compile($ruleTemplate, $asset);

    $this->runRuleAsync($rule);
  }

  public function runRuleSync(RuleInterface $rule) {
    $this->ruleRunner->run($rule);
  }

  public function runRuleAsync(RuleInterface $rule) {
    $command = sprintf(
        static::RUN_COMMAND,
        json_encode([$this->ruleNormalizer->normalize($rule, 'array')])
    );

    $this->commandLauncher->executeBackground($command);
  }
}
