<?php

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\Domain\Rule;
use Akeneo\CouplingDetector\Domain\RuleInterface;

$finder = new DefaultFinder();
$finder->notPath('Oro');
$finder->notPath('Acme');
$finder->notPath('spec');
$finder->notPath('tests');

const USER_MANAGEMENT_NAMESPACE = 'Akeneo\UserManagement';
const PIM_NAMESPACE = 'Akeneo\Pim';
const PAM_NAMESPACE = 'Akeneo\Pam';
const MDM_NAMESPACE = 'Akeneo\Mdm';
const TARGET_MARKET_NAMESPACE = 'Akeneo\TargetMarket';

$rootRules = [
    new Rule(
        USER_MANAGEMENT_NAMESPACE,
        [
            PIM_NAMESPACE,
            PAM_NAMESPACE,
            MDM_NAMESPACE,
            TARGET_MARKET_NAMESPACE,
        ],
        RuleInterface::TYPE_FORBIDDEN
    ),
    new Rule(
        TARGET_MARKET_NAMESPACE,
        [
            PIM_NAMESPACE,
            PAM_NAMESPACE,
            MDM_NAMESPACE,
            USER_MANAGEMENT_NAMESPACE,
        ],
        RuleInterface::TYPE_FORBIDDEN
    ),
    new Rule(MDM_NAMESPACE, [PIM_NAMESPACE], RuleInterface::TYPE_FORBIDDEN),
    new Rule(
        PAM_NAMESPACE,
        [PIM_NAMESPACE, MDM_NAMESPACE],
        RuleInterface::TYPE_FORBIDDEN
    ),
];

const PIM_ENRICHMENT = 'Akeneo\Pim\Enrichment';
const PIM_STRUCTURE = 'Akeneo\Pim\Structure';
const PIM_SECURITY = 'Akeneo\Pim\Security';
const PIM_AUTOMATION = 'Akeneo\Pim\Automation';
const PIM_WORKORG = 'Akeneo\Pim\WorkOrganisation';
const PIM_WORKORG_TWA = 'Akeneo\Pim\WorkOrganisation\TeamWorkAssistant';
const PIM_WORKORG_WFL = 'Akeneo\Pim\WorkOrganisation\Workflow';

$pimRules = [
    new Rule(PIM_STRUCTURE, [PIM_ENRICHMENT, PIM_AUTOMATION, PIM_WORKORG, PIM_SECURITY], RuleInterface::TYPE_FORBIDDEN),
    new Rule(PIM_ENRICHMENT, [PIM_AUTOMATION, PIM_WORKORG, PIM_SECURITY], RuleInterface::TYPE_FORBIDDEN),
    new Rule(PIM_WORKORG_TWA, [PIM_WORKORG_WFL, PIM_AUTOMATION], RuleInterface::TYPE_FORBIDDEN),
    new Rule(PIM_WORKORG_WFL, [PIM_WORKORG_TWA, PIM_AUTOMATION, PIM_STRUCTURE], RuleInterface::TYPE_FORBIDDEN),
    new Rule(PIM_AUTOMATION, [PIM_WORKORG, PIM_STRUCTURE, PIM_SECURITY], RuleInterface::TYPE_FORBIDDEN),
];

$config = new Configuration(array_merge($rootRules, $pimRules), $finder);

return $config;
