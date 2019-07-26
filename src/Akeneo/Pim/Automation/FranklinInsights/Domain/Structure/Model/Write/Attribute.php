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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Model\Write;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeLabel;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeType;

class Attribute
{
    private $code;
    private $label;
    private $type;

    public function __construct(AttributeCode $code, AttributeLabel $label, AttributeType $type)
    {
        $this->code = $code;
        $this->label = $label;
        $this->type = $type;
    }

    public function getCode(): AttributeCode
    {
        return $this->code;
    }

    public function getLabel(): AttributeLabel
    {
        return $this->label;
    }

    public function getType(): AttributeType
    {
        return $this->type;
    }
}
