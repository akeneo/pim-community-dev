<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$builder = new RuleBuilder();

$rules = [
    $builder->only(
        [
            'Webmozart\Assert\Assert',
            'Akeneo\Platform\TailoredImport\Domain',
        ],
    )->in('Akeneo\Platform\TailoredImport\Application'),

    $builder->only(
        [
            'Webmozart\Assert\Assert',
        ],
    )->in('Akeneo\Platform\TailoredImport\Domain'),

    $builder->only(
        [
            'Symfony\Component',
            'Akeneo\Platform\TailoredImport\Application',
            'Akeneo\Platform\TailoredImport\Domain',
            'Akeneo\Tool',
        ],
    )->in('Akeneo\Platform\TailoredImport\Infrastructure'),
];

return new Configuration($rules, $finder);
