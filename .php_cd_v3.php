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

$config = new Configuration($rootRules, $finder);

return $config;
