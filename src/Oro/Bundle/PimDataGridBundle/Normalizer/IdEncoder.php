<?php

declare(strict_types=1);

namespace Oro\Bundle\PimDataGridBundle\Normalizer;

/**
 * Id encoder to manipulate product and product model ids
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdEncoder
{
    public const PRODUCT_TYPE = 'product';
    public const PRODUCT_MODEL_TYPE = 'product_model';

    /**
     * Encode id and type to a type_id format.
     *
     * @param string $type
     * @param int    $id
     *
     * @return string
     */
    public static function encode(string $type, int $id): string
    {
        return sprintf('%s_%s', $type, $id);
    }

    /**
     * Decode the type_id format into id and type values
     *
     * @param  string $encodedId
     *
     * @return array
     */
    public static function decode(string $encodedId): array
    {
        $type = 1 !== preg_match(sprintf('/^%s_/', self::PRODUCT_MODEL_TYPE), $encodedId) ?
            self::PRODUCT_TYPE :
            self::PRODUCT_MODEL_TYPE;

        return [
            'id'   => intval(str_replace($type . '_', '', $encodedId)),
            'type' => $type,
        ];
    }
}
