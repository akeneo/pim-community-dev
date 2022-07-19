<?php

namespace Akeneo\Pim\Structure\Family\API\Model;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @psalm-immutable
 */
class FamilyWithLabelsCollection
{
    public function __construct(
        public array $familiesWithLabels
    ) {
        Assert::allIsInstanceOf($familiesWithLabels, FamilyWithLabels::class);
    }
}
