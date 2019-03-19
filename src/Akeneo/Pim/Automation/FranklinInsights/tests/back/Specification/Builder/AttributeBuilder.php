<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\FranklinInsights\Specification\Builder;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Model\Read\Attribute;
use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributeBuilder
{
    private $code;

    private $type;

    private $isScopable;

    private $isLocalizable;

    private $decimalsAllowed;

    private $isLocaleSpecific;

    private $labels;

    private $metricFamily;

    private $defaultMetricUnit;

    public function __construct()
    {
        $this->code = new AttributeCode('code');
        $this->type = AttributeTypes::TEXT;
        $this->isScopable = false;
        $this->isLocalizable = false;
        $this->decimalsAllowed = false;
        $this->isLocaleSpecific = false;
        $this->labels = [];
        $this->metricFamily = null;
        $this->defaultMetricUnit = null;
    }

    public function build(): Attribute
    {
        return new Attribute($this->code, 1, $this->type, $this->isScopable, $this->isLocalizable, $this->decimalsAllowed, $this->isLocaleSpecific, $this->labels, $this->metricFamily, $this->defaultMetricUnit);
    }

    public function isScopable()
    {
        $this->isScopable = true;

        return $this;
    }

    public function isLocalizable()
    {
        $this->isLocalizable = true;

        return $this;
    }

    public function decimalsAllowed(bool $decimalsAllowed)
    {
        $this->decimalsAllowed = $decimalsAllowed;

        return $this;
    }

    public function isLocaleSpecific()
    {
        $this->isLocaleSpecific = true;

        return $this;
    }

    public function withCode(string $code)
    {
        $this->code = new AttributeCode($code);

        return $this;
    }

    public function withType(string $type)
    {
        $this->type = $type;

        return $this;
    }

    public function withLabels(array $labels)
    {
        $this->labels = $labels;

        return $this;
    }

    public function withMetricFamily(string $metricFamily)
    {
        $this->metricFamily = $metricFamily;

        return $this;
    }

    public function withDefaultMetricUnit(string $defaultMetricUnit)
    {
        $this->defaultMetricUnit = $defaultMetricUnit;

        return $this;
    }

    public static function fromCode(string $code): Attribute
    {
        return new Attribute(new AttributeCode($code), 1, AttributeTypes::TEXT, false, false, false, false, [], null, null);
    }
}
