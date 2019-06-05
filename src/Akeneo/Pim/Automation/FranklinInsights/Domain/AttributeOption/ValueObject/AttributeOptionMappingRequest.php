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

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeOptionCode;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributeOptionMappingRequest
{
    /** @var string */
    private $franklinAttributeOptionLabel;

    /** @var AttributeOptionCode|null */
    private $pimAttributeOptionCode;

    /** @var int */
    private $status;

    public function __construct(string $franklinAttributeOptionLabel, ?AttributeOptionCode $pimAttributeOptionCode, int $status)
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
     * @return string|null
     */
    public function getPimAttributeOptionCode(): ?AttributeOptionCode
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
