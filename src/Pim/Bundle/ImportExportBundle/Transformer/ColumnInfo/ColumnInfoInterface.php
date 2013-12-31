<?php

namespace Pim\Bundle\ImportExportBundle\Transformer\ColumnInfo;

use Pim\Bundle\CatalogBundle\Model\ProductAttributeInterface;

/**
 * Interface for column info
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ColumnInfoInterface
{
    /**
     * Sets the attribute
     *
     * @param ProductAttributeInterface $attribute
     *
     * @throws \Pim\Bundle\ImportExportBundle\Exception\ColumnLabelException
     */
    public function setAttribute(ProductAttributeInterface $attribute = null);

    /**
     * Get the full label
     *
     * @return string
     */
    public function getLabel();

    /**
     * Get the column name stripped of suffixes
     *
     * @return string
     */
    public function getName();

    /**
     * Get the column's property path
     *
     * @return string
     */
    public function getPropertyPath();

    /**
     * Return the column's locale (only available if the attribute has been set)
     *
     * @return get the column locale
     */
    public function getLocale();

    /**
     * Returns the column's scope
     *
     * @return type
     */
    public function getScope();

    /**
     * Return the column's suffixes
     *
     * @return array
     */
    public function getSuffixes();

    /**
     * Returns the associated attribute
     *
     * @return ProductAttributeInterface
     */
    public function getAttribute();
}
