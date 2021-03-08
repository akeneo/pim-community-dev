<?php

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$finder->notPath('tests');
$builder = new RuleBuilder();

$rules = [
    $builder->only(
        [
            // External dependencies
            'Symfony\Component',
            'Psr\Log\LoggerInterface',
            'Ramsey\Uuid\Uuid',
            'Webmozart\Assert\Assert',
            'Hslavich\OneloginSamlBundle\Security',

            // Akeneo common dependencies
            'Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag',
            'Akeneo\Tool\Component',

            // For External Javascript Dependencies
            'Akeneo\Platform\Bundle\UIBundle\Provider\ContentSecurityPolicy\ContentSecurityPolicyProviderInterface',
            'Akeneo\Platform\Bundle\UIBundle\Provider\ExternalDependencyProviderInterface',
            'Akeneo\Platform\Bundle\UIBundle\EventListener\ScriptNonceGenerator',

            // For SSO authentication
            'Akeneo\Platform\Bundle\AuthenticationBundle\Sso\User\UnknownUserException',
        ]
    )->in('Akeneo\Pim\TrialEdition\Infrastructure'),
];

return new Configuration($rules, $finder);
