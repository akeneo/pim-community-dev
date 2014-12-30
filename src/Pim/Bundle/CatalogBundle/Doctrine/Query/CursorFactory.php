<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Query;

/**
 * Class CursorFactory to iterate product in bulk
 *
 * @author    Stephane Chapeau <stephane.chapeau@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CursorFactory implements CursorFactoryInterface
{
    /** @var string */
    private $productCursorClass = null;

    /**
     * @param $productCursorClass
     */
    public function __construct(
        $productCursorClass
    ) {
        $this->productCursorClass = $productCursorClass;
    }

    /**
     * {@inheritdoc}
     */
    public function createProductCursor($queryBuilder)
    {
        return new $this->productCursorClass($queryBuilder);
    }
}
