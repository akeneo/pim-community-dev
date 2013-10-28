<?php

namespace Pim\Bundle\ImportExportBundle\Cache;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\Entity\Family;

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
    
    protected $doctrine;
    
    protected $attributes;
    /**
     * @var array
     */
    protected $columns;
    
    protected $identifierAttribute;
    
    protected $initialized=false;

    public function __construct(RegistryInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }
    public function reset()
    {
        $this->attributes = null;
        $this->columns = null;
        $this->identifierAttribute = null;
        $this->initialized = false;
    }
    public function initialize($columnLabels)
    {
        $columnLabelTokens = $this->getColumnLabelTokens($columnLabels);
        $this->setAttributes($columnLabelTokens);
        $this->setColumns($columnLabelTokens);
        $this->initialized = true;
    }
    public function isInitialized() {
        return $this->initialized;
    }
    public function getAttribute($code) {
        foreach($this->attributes as $attribute) {
            if ($code == $attribute->getCode()) {
                return $attribute;
            }
        }
    }
    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getIdentifierAttribute()
    {
        return $this->identifierAttribute;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    protected function getColumnLabelTokens($columnLabels)
    {
        $columnTokens = array();
        foreach($columnLabels as $columnLabel) {
            $columnTokens[$columnLabel] = explode('-', $columnLabel);
        }

        return $columnTokens;
    }
    protected function setColumns($columnLabelTokens)
    {
        $this->columns = array();
        foreach($columnLabelTokens as $columnCode => $labelTokens) {
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
        if(count($this->attributes) != count($codes)) {
            throw new \Exception(
                sprintf(
                    'The following fields do not exist : %s',
                    implode(
                        ', ', 
                        array_diff(
                            $codes, 
                            array_map(function($attribute) { return $attribute->getCode(); }, $this->attributes)
                        )
                    )
                )
            );
        }
    }
}
