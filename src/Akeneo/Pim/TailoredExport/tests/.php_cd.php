<?php

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();

$builder = new RuleBuilder();

$rules = [
    $builder->only([
        'Symfony\Component',
        'Symfony\Contracts',
        'Akeneo\Tool',
        'Oro\Bundle\SecurityBundle\Annotation\AclAncestor',
        'Oro\Bundle\SecurityBundle\SecurityFacade',
        'Webmozart\Assert\Assert',

        'Akeneo\Pim\Structure\Component\Query\PublicApi',
        'Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators'
    ])->in('Akeneo\Pim\TailoredExport'),
];

return new Configuration($rules, $finder);
