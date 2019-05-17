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

    /** @var array */
    private $attributeProperties;

    public function __construct(string $attributeCode, string $attributeType, array $attributeProperties)
    {
        $this->attributeCode = $attributeCode;
        $this->attributeType = $attributeType;
        $this->attributeProperties = $attributeProperties;
    }

    public function code(): string
    {
        return $this->attributeCode;
    }

    public function type(): string
    {
        return $this->attributeType;
    }

    public function properties(): array
    {
        return $this->attributeProperties;
    }
}
