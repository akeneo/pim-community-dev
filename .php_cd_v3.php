<?php

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$finder->notPath('Oro');
$finder->notPath('Acme');
$finder->notPath('spec');
$finder->notPath('tests');

$builder = new RuleBuilder();

const USER_MANAGEMENT_NAMESPACE = 'Akeneo\UserManagement';
const PIM_NAMESPACE = 'Akeneo\Pim';
const PAM_NAMESPACE = 'Akeneo\Pam';
const MDM_NAMESPACE = 'Akeneo\Mdm';
const TARGET_MARKET_NAMESPACE = 'Akeneo\TargetMarket';

$rootRules = [
    $builder
        ->forbids([PIM_NAMESPACE, PAM_NAMESPACE, MDM_NAMESPACE, TARGET_MARKET_NAMESPACE])
        ->in(USER_MANAGEMENT_NAMESPACE),
    $builder
        ->forbids([PIM_NAMESPACE, PAM_NAMESPACE, MDM_NAMESPACE, USER_MANAGEMENT_NAMESPACE])
        ->in(TARGET_MARKET_NAMESPACE),
    $builder->forbids([PIM_NAMESPACE])->in(MDM_NAMESPACE),
    $builder->forbids([PIM_NAMESPACE, MDM_NAMESPACE])->in(PAM_NAMESPACE),
];

const PIM_ENRICHMENT = 'Akeneo\Pim\Enrichment';
const PIM_STRUCTURE = 'Akeneo\Pim\Structure';
const PIM_SECURITY = 'Akeneo\Pim\Security';
const PIM_AUTOMATION = 'Akeneo\Pim\Automation';
const PIM_WORKORG = 'Akeneo\Pim\WorkOrganisation';
const PIM_WORKORG_TWA = 'Akeneo\Pim\WorkOrganisation\TeamWorkAssistant';
const PIM_WORKORG_WFL = 'Akeneo\Pim\WorkOrganisation\Workflow';

$pimRules = [
    $builder
        ->forbids([PIM_ENRICHMENT, PIM_AUTOMATION, PIM_WORKORG, PIM_SECURITY])
        ->in(PIM_STRUCTURE),
    $builder
        ->forbids([PIM_AUTOMATION, PIM_WORKORG, PIM_SECURITY])
        ->in(PIM_ENRICHMENT),
    $builder
        ->forbids([PIM_WORKORG_WFL, PIM_AUTOMATION])
        ->in(PIM_WORKORG_TWA),
    $builder
        ->forbids([PIM_WORKORG_TWA, PIM_AUTOMATION, PIM_STRUCTURE])
        ->in(PIM_WORKORG_WFL),
    $builder
        ->forbids([PIM_WORKORG, PIM_STRUCTURE, PIM_SECURITY])
        ->in(PIM_AUTOMATION),
];

$config = new Configuration(array_merge($rootRules, $pimRules), $finder);

return $config;
