<?php

namespace Context;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Pim\Behat\Context\PimContext;

/**
 * Context for data transformations
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TransformationContext extends PimContext
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
     * @Transform /^product model "([^"]*)"$/
     *
     * @return ProductModel
     */
    public function castProductModelCodeToProductModel($code)
    {
        return $this->getFixturesContext()->getProductModel($code);
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
     * @Transform /^channel "([^"]*)"$/
     *
     * @return Channel
     */
    public function castChannelCodeToChannel($code)
    {
        return $this->getFixturesContext()->getChannel($code);
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
     * @Transform /^"([^"]*)" product group$/
     *
     * @return GroupInterface
     */
    public function castProductGroupCodeToProductGroup($code)
    {
        return $this->getFixturesContext()->getProductGroup($code);
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
     * @Transform /^"([^"]*)" association type$/
     *
     * @return AssociationType
     */
    public function castAssociationTypeCodeToAssociationType($code)
    {
        return $this->getFixturesContext()->getAssociationType($code);
    }

    /**
     * @param string $label
     *
     * @Transform /^"([^"]*)" role$/
     *
     * @return Role
     */
    public function castRoleLabelToRole($label)
    {
        return $this->getFixturesContext()->getRole($label);
    }

    /**
     * @param string $label
     *
     * @Transform /^"([^"]*)" user group$/
     *
     * @return UserGroup
     */
    public function castUserGroupLabelToUserGroup($label)
    {
        return $this->getFixturesContext()->getUserGroup($label);
    }

    /**
     * @return FixturesContext
     */
    protected function getFixturesContext()
    {
        return $this->getMainContext()->getSubcontext('fixtures');
    }
}
