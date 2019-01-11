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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\ValueObject;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributeOptionMappingRequest
{
    /** @var string */
    private $franklinAttributeOptionLabel;

    /** @var null|string */
    private $pimAttributeOptionCode;

    /** @var int */
    private $status;

    /**
     * @param string $franklinAttributeOptionLabel
     * @param null|string $pimAttributeOptionCode
     * @param int $status
     */
    public function __construct(string $franklinAttributeOptionLabel, ?string $pimAttributeOptionCode, int $status)
    {
        $this->franklinAttributeOptionLabel = $franklinAttributeOptionLabel;
        $this->pimAttributeOptionCode = $pimAttributeOptionCode;
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getFranklinAttributeOptionLabel(): string
    {
        return $this->franklinAttributeOptionLabel;
    }

    /**
     * @return null|string
     */
    public function getPimAttributeOptionCode(): ?string
    {
        return $this->pimAttributeOptionCode;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }
}
