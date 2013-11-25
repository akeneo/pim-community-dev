<?php

namespace Pim\Bundle\ImportExportBundle\Cache;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Model\Group;

/**
 * Caches the attributes of an import. Do not forget to call the reset method between two imports.
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeCache
{
    /**
     * @staticvar the identifier attribute type
     */
    const IDENTIFIER_ATTRIBUTE_TYPE = 'pim_catalog_identifier';

    /**
     * @var RegistryInterface
     */
    protected $doctrine;

    /**
     * @var array
     */
    protected $attributes;
    /**
     * @var array
     */
    protected $columns;

    /**
     * @var ProductAttribute
     */
    protected $identifierAttribute;

    /**
     * @var boolean
     */
    protected $initialized=false;

    /**
     * @var array
     */
    protected $familyAttributeCodes = array();

    /***
     * @var array
     */
    protected $groupAttributeCodes = array();

    /**
     * Constructor
     * @param RegistryInterface $doctrine
     */
    public function __construct(RegistryInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * Clears the cache
     */
    public function clear()
    {
        $this->attributes = null;
        $this->columns = null;
        $this->identifierAttribute = null;
        $this->initialized = false;
    }

    /**
     * Initializes the cache with a set of column labels
     *
     * @param array $columnLabels
     */
    public function initialize(array $columnLabels)
    {
        $columnLabelTokens = $this->getColumnLabelTokens($columnLabels);
        $this->setAttributes($columnLabelTokens);
        $this->setColumns($columnLabelTokens);
        $this->initialized = true;
    }

    /**
     * Returns true if the cache has been initialized
     *
     * @return boolean
     */
    public function isInitialized()
    {
        return $this->initialized;
    }

    /**
     * Returns the attribute corresponding to the specified code
     *
     * @param string $code
     *
     * @return ProductAttribute
     */
    public function getAttribute($code)
    {
        foreach ($this->attributes as $attribute) {
            if ($code === $attribute->getCode()) {
                return $attribute;
            }
        }
    }

    /**
     * Returns an array of cached attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Returns the product attribute
     *
     * @return ProductAttribute
     */
    public function getIdentifierAttribute()
    {
        return $this->identifierAttribute;
    }

    /**
     * Returns an array of information about the columns
     *
     * The following info is returned for each column :
     *
     * columnLabel:
     *      attribute:  A ProductAttribute instance
     *      code:       The code of the attribute
     *      locale:     The locale of the column
     *      scope:      The scope of the column
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Returns the required attribute codes for a product
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    public function getRequiredAttributeCodes(ProductInterface $product)
    {
        $codes = array();

        if ($product->getFamily()) {
            $codes = $this->getFamilyAttributeCodes($product->getFamily());
        }

        foreach ($product->getGroups() as $group) {
            $codes = array_merge($codes, $this->getGroupAttributeCodes($group));
        }

        if ($product->getId()) {
            foreach ($product->getValues() as $value) {
                $codes[] = $value->getAttribute()->getCode();
            }
        }

        return array_unique($codes);
    }

    /**
     * Returns the attribute codes for a group
     *
     * @param Group $group
     *
     * @return array
     */
    protected function getGroupAttributeCodes(Group $group)
    {
        $code = $group->getCode();
        if (!isset($this->groupAttributeCodes[$code])) {
            $this->groupAttributeCodes[$code] = $this->getAttributeCodes($group);
        }

        return $this->groupAttributeCodes[$code];
    }

    /**
     * Returns the attribute codes for a family
     *
     * @param Family $family
     *
     * @return array
     */
    protected function getFamilyAttributeCodes(Family $family)
    {
        $code = $family->getCode();
        if (!isset($this->familyAttributeCodes[$code])) {
            $this->familyAttributeCodes[$code] = $this->getAttributeCodes($family);
        }

        return $this->familyAttributeCodes[$code];
    }

    /**
     * Returns the attribute codes for an object
     *
     * @param object $object
     *
     * @return array
     */
    protected function getAttributeCodes($object)
    {
        return array_map(
            function ($attribute) {
                return $attribute->getCode();
            },
            $object->getAttributes()->toArray()
        );
    }

    /**
     * Returns an array of tokens for each column label.
     *
     * @param array $columnLabels
     *
     * @return array
     */
    protected function getColumnLabelTokens($columnLabels)
    {
        $columnTokens = array();
        foreach ($columnLabels as $columnLabel) {
            $columnTokens[$columnLabel] = explode('-', $columnLabel);
        }

        return $columnTokens;
    }

    /**
     * Sets the columns property
     *
     * @param array $columnLabelTokens
     *
     * @throws \InvalidArgumentException
     */
    protected function setColumns(array $columnLabelTokens)
    {
        $this->columns = array();
        foreach ($columnLabelTokens as $columnCode => $labelTokens) {
            $columnInfo = array(
                'code' => array_shift($labelTokens),
                'locale' => null,
                'scope' => null
            );
            $columnInfo['attribute'] = $this->getAttribute($columnInfo['code']);
            if ($columnInfo['attribute']->getTranslatable()) {
                if (!count($labelTokens)) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'The column "%s" must contain the local code',
                            $columnCode
                        )
                    );
                }
                $columnInfo['locale'] = array_shift($labelTokens);
            }
            if ($columnInfo['attribute']->getScopable()) {
                if (!count($labelTokens)) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'The column "%s" must contain the scope code',
                            $columnCode
                        )
                    );
                }
                $columnInfo['scope']  = array_shift($labelTokens);
            }
            $this->columns[$columnCode] = $columnInfo;
        }
    }

    /**
     * Sets the attributes and identifierAttributes properties
     *
     * @param array $columnLabelTokens
     *
     * @return null
     * @throws \InvalidArgumentException
     */
    protected function setAttributes($columnLabelTokens)
    {
        $codes = array_unique(
            array_map(
                function ($tokens) {
                    return $tokens[0];
                },
                $columnLabelTokens
            )
        );

        $this->attributes = $this->doctrine->getRepository('PimCatalogBundle:ProductAttribute')
                ->findBy(array('code' => $codes));

        foreach ($this->attributes as $attribute) {
            if (static::IDENTIFIER_ATTRIBUTE_TYPE === $attribute->getAttributeType()) {
                $this->identifierAttribute = $attribute;
                break;
            }
        }
        if (count($this->attributes) !== count($codes)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The following fields do not exist: %s',
                    implode(
                        ', ',
                        array_diff(
                            $codes,
                            array_map(
                                function ($attribute) {
                                    return $attribute->getCode();

                                },
                                $this->attributes
                            )
                        )
                    )
                )
            );
        }
    }
}
