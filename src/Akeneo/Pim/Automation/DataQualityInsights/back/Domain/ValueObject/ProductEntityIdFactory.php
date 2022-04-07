<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
public class ProductEntityIdFactory
{
    /**
     * @param 'product'|'product_model' $type
     * @param string id
     * @return ProductEntityIdInterface
     */
    public static function create(string $type, string $id) : ProductEntityIdInterface{
        switch ($type) {
            case 'product':
                if (!is_int($id)) {
                    throw new \InvalidArgumentException("can't create a ProductUuid from a non-integer id");
                }
                return new ProductUuid($id);
            case 'product_model':
                if (!is_int($id)) {
                    throw new \InvalidArgumentException("can't create a ProductModelId from a non-integer id");
                }
                return new ProductModelId();
            default:
                throw new \RuntimeException(sprintf("Can't build unknown ProductEntityIdInterface for type %s",$type));
        }
    }
}
