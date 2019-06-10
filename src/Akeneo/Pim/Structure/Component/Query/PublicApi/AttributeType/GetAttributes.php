<?php
declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
interface GetAttributes
{
    /**
     * It returns an array of attributes indexed by the code.
     * If an attribute is not found, it returns a NULL value for this key.
     *
     * @return Attribute[]
     */
    public function forCodes(array $attributeCodes): array;

    public function forCode(string $attributeCode): ?Attribute;
}
