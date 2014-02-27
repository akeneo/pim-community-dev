<?php

namespace Pim\Bundle\TransformBundle\Transformer\ColumnInfo;

use Doctrine\Common\Util\Inflector;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\TransformBundle\Exception\ColumnLabelException;

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
     * @var AbstractAttribute
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
     * @param AbstractAttribute $attribute
     *
     * @throws ColumnLabelException
     */
    public function setAttribute(AbstractAttribute $attribute = null)
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
            if ($attribute->isLocalizable()) {
                if (count($suffixes)) {
                    $this->locale = array_shift($suffixes);
                } else {
                    throw new ColumnLabelException(
                        'The column "%column%" must contain a locale code',
                        array('%column%' => $this->label)
                    );
                }
            }
            if ($attribute->isScopable()) {
                if (count($suffixes)) {
                    $this->scope = array_shift($suffixes);
                } else {
                    throw new ColumnLabelException(
                        'The column "%column%" must contain a scope code',
                        array('%column%' => $this->label)
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
