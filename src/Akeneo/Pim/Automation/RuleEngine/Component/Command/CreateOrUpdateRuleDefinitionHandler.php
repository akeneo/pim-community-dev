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

use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionTranslationInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class CreateOrUpdateRuleDefinitionHandler
{
    /** @var RuleDefinitionRepositoryInterface */
    private $ruleDefinitionRepository;

    /** @var DenormalizerInterface */
    private $ruleDenormalizer;

    /** @var SaverInterface */
    private $ruleDefinitionSaver;

    /** @var string */
    private $ruleClass;

    /** @var string */
    private $ruleDefinitionClass;

    public function __construct(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        DenormalizerInterface $ruleDenormalizer,
        SaverInterface $ruleDefinitionSaver,
        string $ruleClass,
        string $ruleDefinitionClass
    ) {
        $this->ruleDefinitionRepository = $ruleDefinitionRepository;
        $this->ruleDenormalizer = $ruleDenormalizer;
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

        $ruleDefinition->setCode($rule->getCode());
        $ruleDefinition->setPriority($rule->getPriority());
        $ruleDefinition->setType($rule->getType());
        $ruleDefinition->setContent($rule->getContent());
        foreach ($rule->getTranslations() as $translation) {
            /** @var $translation RuleDefinitionTranslationInterface */
            $ruleDefinition->setLabel($translation->getLocale(), $translation->getLabel());
        };

        $this->ruleDefinitionSaver->save($ruleDefinition);
    }
}
