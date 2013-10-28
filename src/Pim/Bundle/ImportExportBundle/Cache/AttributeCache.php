<?php

namespace Pim\Bundle\ImportExportBundle\Cache;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;

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
     * @param  string           $code
     * @return ProductAttribute
     */
    public function getAttribute($code)
    {
        foreach ($this->attributes as $attribute) {
            if ($code == $attribute->getCode()) {
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
     * Returns an array of tokens for each column label.
     *
     * @param  array $columnLabels
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
     * @param  array      $columnLabelTokens
     * @throws \Exception
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
                    throw new \Exception(
                        sprintf(
                            'The column "%s" must contains the local code',
                            $key
                        )
                    );
                }
                $columnInfo['locale'] = array_shift($labelTokens);
            }
            if ($columnInfo['attribute']->getScopable()) {
                if (!count($labelTokens)) {
                    throw new \Exception(
                        sprintf(
                            'The column "%s" must contains the scope code',
                            $key
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
     * @param  array      $columnLabelTokens
     * @throws \Exception
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
            if (static::IDENTIFIER_ATTRIBUTE_TYPE == $attribute->getAttributeType()) {
                $this->identifierAttribute = $attribute;
                break;
            }
        }
        if (count($this->attributes) != count($codes)) {
            throw new \Exception(
                sprintf(
                    'The following fields do not exist : %s',
                    implode(
                        ', ',
                        array_diff(
                            $codes,
                            array_map(function ($attribute) { return $attribute->getCode(); }, $this->attributes)
                        )
                    )
                )
            );
        }
    }
}
