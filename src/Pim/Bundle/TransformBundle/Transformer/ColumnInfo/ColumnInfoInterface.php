<?php

namespace Pim\Bundle\TransformBundle\Transformer\ColumnInfo;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

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
     * @param AttributeInterface $attribute
     *
     * @throws \Pim\Bundle\TransformBundle\Exception\ColumnLabelException
     */
    public function setAttribute(AttributeInterface $attribute = null);

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
     * @return string the column locale
     */
    public function getLocale();

    /**
     * Returns the column's scope
     *
     * @return string
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
     * @return AttributeInterface
     */
    public function getAttribute();
}
