<?php
namespace Akeneo\CatalogBundle\Document;

use Akeneo\CatalogBundle\Doctrine\EntityTypeManager;

/**
 * Manager of flexible product type stored with doctrine documents
 *
 * The product type service, a builder which allows to embed complexity of
 * CRUD operation, of persistence and revisioning of the flexible entity type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductTypeManager extends EntityTypeManager
{

    /**
     * Get groups code
     * @return Array
     */
    public function getGroupsCodes()
    {
        return array_keys($this->getObject()->getGroups());
    }

    /**
     * Add a group to a product type
     *
     * @param string $groupCode
     * @return ProductType
     */
    public function addGroup($groupCode)
    {
        $this->getObject()->addGroup($groupCode);
        return $this;
    }

    /**
     * Get group
     * @param string $groupCode
     */
    public function getGroup($groupCode)
    {
        return $this->getObject()->getGroup($groupCode);
    }

    /**
     * Remove group
     * @param string $groupCode
     */
    public function removeGroup($groupCode)
    {
        return $this->getObject()->removeGroup($groupCode);
    }

    /**
     * Add a field to the type
     *
     * @param string $fieldCode
     * @param string $fieldType
     * @param string $groupCode
     * @param $string $title
     * @return ProductType
     */
    public function addField($fieldCode, $fieldType, $groupCode, $title = null)
    {
        // check if field already exists
        $field = $this->getField($fieldCode);
        // create a new field
        if (!$field) {
            $field = new ProductFieldMongo();
            $field->setCode($fieldCode);
            $field->setType($fieldType);
            $field->setTitle($title);
        }
        // add field to group
        $this->getObject()->addFieldToGroup($field, $groupCode);
        return $this;
    }

    /**
     * Get field by code
     *
     * @param string $fieldCode
     */
    public function getField($fieldCode)
    {
        $field = $this->getManager()->getRepository('AkeneoCatalogBundle:ProductFieldMongo')
            ->findOneByCode($fieldCode);
        return $field;
    }

    /**
     * Remove field
     *
     * @param $code
     */
    public function removeField($fieldCode)
    {
        // TODO: deal if not already persisted
        // TODO remove from group
        $field = $this->getField($fieldCode);
        $this->getManager()->remove($field);
    }

    /**
     * Remove field from type
     *
     * @param $code
     */
    public function removeFieldFromType($fieldCode)
    {
        $this->getObject()->removeField($fieldCode);

    }

    /**
     * Refresh type state from database
     * @return ProductType
     */
    public function refresh()
    {
        // TODO : problem with groups and fields code arrays ?
        // TODO : deal with locale
        $this->getManager()->refresh($this->getObject());
        return $this;
    }

    /**
    * Change locale and refresh data for this locale
    *
    * @param string $locale
    */
    public function switchLocale($locale)
    {
        $this->getObject()->setTranslatableLocale($locale);
        foreach ($this->getObject()->getFields() as $field) {
            $field->setTranslatableLocale($locale);
        }
    }

    /**
     * @see newEntityInstance
     * @return ProductManager
     */
    public function newProductInstance()
    {
        return parent::newEntityInstance();
    }
}