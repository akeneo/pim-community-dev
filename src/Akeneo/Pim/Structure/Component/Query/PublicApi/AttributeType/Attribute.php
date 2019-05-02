<?php
declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class Attribute
{
    /** @var string */
    private $attributeCode;

    /** @var string */
    private $attributeType;

    public function __construct(string $attributeCode, string $attributeType)
    {
        $this->attributeCode = $attributeCode;
        $this->attributeType = $attributeType;
    }

    public function code(): string
    {
        return $this->attributeCode;
    }

    public function type(): string
    {
        return $this->attributeType;
    }
}
