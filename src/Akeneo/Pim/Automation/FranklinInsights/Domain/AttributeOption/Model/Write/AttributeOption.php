<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Write;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributeOption
{
    /** @var string */
    private $franklinOptionId;

    /** @var string */
    private $franklinOptionLabel;

    /** @var string|null */
    private $pimOptionId;

    /**
     * @param string $franklinOptionId
     * @param string $franklinOptionLabel
     * @param string|null $pimOptionId
     */
    public function __construct(
        string $franklinOptionId,
        string $franklinOptionLabel,
        ?string $pimOptionId = null
    ) {
        $this->franklinOptionId = $franklinOptionId;
        $this->franklinOptionLabel = $franklinOptionLabel;
        $this->pimOptionId = $pimOptionId;
    }

    /**
     * @return string
     */
    public function getFranklinOptionId(): string
    {
        return $this->franklinOptionId;
    }

    /**
     * @return string
     */
    public function getFranklinOptionLabel(): string
    {
        return $this->franklinOptionLabel;
    }

    /**
     * @return string|null
     */
    public function getPimOptionId(): ?string
    {
        return $this->pimOptionId;
    }

    /**
     * @return bool
     */
    public function isMapped(): bool
    {
        return !empty($this->pimOptionId);
    }
}
