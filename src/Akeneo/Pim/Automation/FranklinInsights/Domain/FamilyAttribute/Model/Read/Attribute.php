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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Model\Read;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class Attribute
{
    /** @var AttributeCode */
    private $code;

    /** @var int */
    private $id;

    /** @var string */
    private $type;

    /** @var bool */
    private $isScopable;

    /** @var bool */
    private $isLocalizable;

    /** @var bool */
    private $decimalsAllowed;

    /** @var bool */
    private $isLocaleSpecific;

    /** @var array */
    private $labels;

    /** @var string|null */
    private $metricFamily;

    /** @var string|null */
    private $defaultMetricUnit;

    public function __construct(
        AttributeCode $code, int $id, string $type, bool $isScopable, bool $isLocalizable, bool $decimalsAllowed,
        bool $isLocaleSpecific, array $labels, ?string $metricFamily, ?string $defaultMetricUnit
    ) {
        $this->code = $code;
        $this->id = $id;
        $this->type = $type;
        $this->isScopable = $isScopable;
        $this->isLocalizable = $isLocalizable;
        $this->decimalsAllowed = $decimalsAllowed;
        $this->isLocaleSpecific = $isLocaleSpecific;
        $this->labels = $labels;
        $this->metricFamily = $metricFamily;
        $this->defaultMetricUnit = $defaultMetricUnit;
    }

    /**
     * @return AttributeCode
     */
    public function getCode(): AttributeCode
    {
        return $this->code;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isScopable(): bool
    {
        return $this->isScopable;
    }

    /**
     * @return bool
     */
    public function isLocalizable(): bool
    {
        return $this->isLocalizable;
    }

    /**
     * @return bool
     */
    public function isDecimalsAllowed(): bool
    {
        return $this->decimalsAllowed;
    }

    /**
     * @return bool
     */
    public function isLocaleSpecific(): bool
    {
        return $this->isLocaleSpecific;
    }

    /**
     * @return array
     */
    public function getLabels(): array
    {
        return $this->labels;
    }

    /**
     * @return string|null
     */
    public function getMetricFamily(): ?string
    {
        return $this->metricFamily;
    }

    /**
     * @return string|null
     */
    public function getDefaultMetricUnit(): ?string
    {
        return $this->defaultMetricUnit;
    }
}
