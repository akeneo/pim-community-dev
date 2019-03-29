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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Read;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;

/**
 * @author Paul Chasle <paul.chasle@akeneo.com>
 */
final class AttributeOption
{
    /** @var string */
    private $code;

    /** @var AttributeCode */
    private $attributeCode;

    /** @var string[] */
    private $translations;

    public function __construct(string $code, AttributeCode $attributeCode, array $translations = [])
    {
        $this->code = $code;
        $this->attributeCode = $attributeCode;
        $this->translations = $translations;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getAttributeCode(): AttributeCode
    {
        return $this->attributeCode;
    }

    public function getTranslations(): array
    {
        return $this->translations;
    }
}
