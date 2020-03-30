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

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeType;

final class Attribute
{
    /** @var AttributeCode */
    private $code;

    /** @var AttributeType */
    private $type;

    /** @var bool */
    private $isLocalizable;

    /** @var bool */
    private $isMainTitle;

    public function __construct(AttributeCode $code, AttributeType $type, bool $isLocalizable, bool $isMainTitle)
    {
        $this->code = $code;
        $this->type = $type;
        $this->isLocalizable = $isLocalizable;
        $this->isMainTitle = $isMainTitle;
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

    public function isMainTitle(): bool
    {
        return $this->isMainTitle;
    }
}
