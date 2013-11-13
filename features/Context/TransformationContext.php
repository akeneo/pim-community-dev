<?php

namespace Context;

use Behat\MinkExtension\Context\RawMinkContext;

/**
 * Context for data transformations
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TransformationContext extends RawMinkContext
{
    /**
     * @param string $sku
     *
     * @Transform /^product "([^"]*)"$/
     *
     * @return Product
     */
    public function castProductSkuToProduct($sku)
    {
        return $this->getFixturesContext()->getProduct($sku);
    }

    /**
     * @param string $code
     *
     * @Transform /^category "([^"]*)"$/
     *
     * @return Category
     */
    public function castCategoryCodeToCategory($code)
    {
        return $this->getFixturesContext()->getCategory($code);
    }

    /**
     * @param string $code
     *
     * @Transform /^"([^"]*)" attribute group$/
     *
     * @return AttributeGroup
     */
    public function castAttributeGroupCodeToAttributeGroup($code)
    {
        return $this->getFixturesContext()->getAttributeGroup($code);
    }

    /**
     * @param string $code
     *
     * @Transform /^"([^"]*)" group type$/
     *
     * @return GroupType
     */
    public function castGroupTypeCodeToGroupType($code)
    {
        return $this->getFixturesContext()->getGroupType($code);
    }

    /**
     * @param string $code
     *
     * @Transform /^"([^"]*)" family$/
     *
     * @return Family
     */
    public function castFamilyCodeToFamily($code)
    {
        return $this->getFixturesContext()->getFamily($code);
    }

    /**
     * @param string $code
     *
     * @Transform /^"([^"]*)" (?:import|export) job$/
     *
     * @return JobInstance
     */
    public function castJobInstanceCodeToJobInstance($code)
    {
        return $this->getFixturesContext()->getJobInstance($code);
    }

    /**
     * @param string $code
     *
     * @Transform /^"([^"]*)" association$/
     *
     * @return Association
     */
    public function castAssociationCodeToAssociation($code)
    {
        return $this->getFixturesContext()->getAssociation($code);
    }

    /**
     * @return FixturesContext
     */
    private function getFixturesContext()
    {
        return $this->getMainContext()->getSubcontext('fixtures');
    }
}
