<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeType;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Attribute
{
    /** @var AttributeCode */
    private $code;

    /** @var AttributeType */
    private $type;

    /** @var bool */
    private $isLocalizable;

    public function __construct(AttributeCode $code, AttributeType $type, bool $isLocalizable)
    {
        $this->code = $code;
        $this->type = $type;
        $this->isLocalizable = $isLocalizable;
    }

    public function getCode(): AttributeCode
    {
        return $this->code;
    }

    public function getType(): AttributeType
    {
        return $this->type;
    }

    public function isLocalizable(): bool
    {
        return $this->isLocalizable;
    }

    public function hasOptions()
    {
        return $this->type->equals(AttributeType::simpleSelect()) || $this->type->equals(AttributeType::multiSelect());
    }
}
