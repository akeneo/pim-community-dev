<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Domain\Model\SourceValue;

use Webmozart\Assert\Assert;

class SimpleAssociationsValue implements SourceValueInterface
{
    /**
     * @return string[]
     */
    private array $associatedProductIdentifiers;

    /**
     * @return string[]
     */
    private array $associatedProductModelCodes;

    /**
     * @return string[]
     */
    private array $associatedGroupCodes;

    public function __construct(
        array $associatedProductIdentifiers,
        array $associatedProductModelCodes,
        array $associatedGroupCodes
    ) {
        Assert::allString($associatedProductIdentifiers);
        Assert::allString($associatedProductModelCodes);
        Assert::allString($associatedGroupCodes);

        $this->associatedProductIdentifiers = $associatedProductIdentifiers;
        $this->associatedProductModelCodes = $associatedProductModelCodes;
        $this->associatedGroupCodes = $associatedGroupCodes;
    }

    /**
     * @return string[]
     */
    public function getAssociatedProductIdentifiers(): array
    {
        return $this->associatedProductIdentifiers;
    }

    /**
     * @return string[]
     */
    public function getAssociatedProductModelCodes(): array
    {
        return $this->associatedProductModelCodes;
    }

    /**
     * @return string[]
     */
    public function getAssociatedGroupCodes(): array
    {
        return $this->associatedGroupCodes;
    }
}
