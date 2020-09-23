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

namespace Akeneo\Pim\Automation\RuleEngine\Component\Command;

use Akeneo\Pim\Automation\RuleEngine\Component\Updater\RuleDefinitionUpdaterInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Doctrine\Common\Saver\RuleDefinitionSaver;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class CreateOrUpdateRuleDefinitionHandler
{
    /** @var RuleDefinitionRepositoryInterface */
    private $ruleDefinitionRepository;

    /** @var DenormalizerInterface */
    private $ruleDenormalizer;

    /** @var RuleDefinitionUpdaterInterface */
    private $ruleDefinitionUpdater;

    /** @var RuleDefinitionSaver */
    private $ruleDefinitionSaver;

    /** @var string */
    private $ruleClass;

    /** @var string */
    private $ruleDefinitionClass;

    public function __construct(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        DenormalizerInterface $ruleDenormalizer,
        RuleDefinitionUpdaterInterface $ruleDefinitionUpdater,
        RuleDefinitionSaver $ruleDefinitionSaver,
        string $ruleClass,
        string $ruleDefinitionClass
    ) {
        $this->ruleDefinitionRepository = $ruleDefinitionRepository;
        $this->ruleDenormalizer = $ruleDenormalizer;
        $this->ruleDefinitionUpdater = $ruleDefinitionUpdater;
        $this->ruleDefinitionSaver = $ruleDefinitionSaver;
        $this->ruleClass = $ruleClass;
        $this->ruleDefinitionClass = $ruleDefinitionClass;
    }

    public function __invoke(CreateOrUpdateRuleCommand $command): void
    {
        $ruleDefinition = $this->ruleDefinitionRepository->findOneByIdentifier($command->code);
        if (null === $ruleDefinition) {
            $ruleDefinition = new $this->ruleDefinitionClass();
        }

        $rule = $this->ruleDenormalizer->denormalize(
            $command->toArray(),
            $this->ruleClass,
            null,
            ['definitionObject' => $ruleDefinition]
        );

        $this->ruleDefinitionUpdater->fromRule($ruleDefinition, $rule);
        $this->ruleDefinitionSaver->save($ruleDefinition);
    }
}
