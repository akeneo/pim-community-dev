<?php
declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
interface GetAttributeByCodes
{
    /**
     * @return Attribute[]
     */
    public function forCodes(array $attributeCodes): array;
}
