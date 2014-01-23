<?php

namespace Pim\Bundle\ImportExportBundle\Transformer\ColumnInfo;

use Doctrine\Common\Util\Inflector;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\ImportExportBundle\Exception\ColumnLabelException;

/**
 * Represents Column information
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ColumnInfo implements ColumnInfoInterface
{
    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $propertyPath;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $scope;

    /**
     * @var array
     */
    protected $suffixes;

    /**
     * @var array
     */
    protected $rawSuffixes;

    /**
     * @var AttributeInterface
     */
    protected $attribute;

    /**
     * Constructor
     *
     * @param string $label
     */
    public function __construct($label)
    {
        $this->label = $label;
        $parts = explode('-', $label);
        $this->name = array_shift($parts);
        $this->propertyPath = lcfirst(Inflector::classify($this->name));
        $this->suffixes = $parts;
        $this->rawSuffixes = $parts;
    }

    /**
     * Sets the attribute
     *
     * @param AttributeInterface $attribute
     *
     * @throws ColumnLabelException
     */
    public function setAttribute(AttributeInterface $attribute = null)
    {
        $this->attribute = $attribute;
        if (null === $attribute) {
            $this->locale = null;
            $this->scope = null;
            $this->suffixes = $this->rawSuffixes;
            $this->propertyPath = lcfirst(Inflector::classify($this->name));
        } else {
            $this->propertyPath = $attribute->getBackendType();
            $suffixes = $this->rawSuffixes;
            if ($attribute->isTranslatable()) {
                if (count($suffixes)) {
                    $this->locale = array_shift($suffixes);
                } else {
                    throw new ColumnLabelException(
                        'The column "%column%" must contain a locale code',
                        ['%column%' => $this->label]
                    );
                }
            }
            if ($attribute->isScopable()) {
                if (count($suffixes)) {
                    $this->scope = array_shift($suffixes);
                } else {
                    throw new ColumnLabelException(
                        'The column "%column%" must contain a scope code',
                        ['%column%' => $this->label]
                    );
                }
            }
            $this->suffixes = $suffixes;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getPropertyPath()
    {
        return $this->propertyPath;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * {@inheritdoc}
     */
    public function getSuffixes()
    {
        return $this->suffixes;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute()
    {
        return $this->attribute;
    }
}
