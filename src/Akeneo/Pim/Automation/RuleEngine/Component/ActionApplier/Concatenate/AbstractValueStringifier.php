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

namespace Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate;

use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
abstract class AbstractValueStringifier implements ValueStringifierInterface
{
    /** @var string[] */
    protected $attributeTypes;

    public function __construct(array $attributeTypes)
    {
        Assert::allString($attributeTypes);
        $this->attributeTypes = $attributeTypes;
    }

    public function forAttributesTypes(): array
    {
        return $this->attributeTypes;
    }
}
