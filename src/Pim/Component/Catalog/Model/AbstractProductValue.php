<?php

namespace Pim\Component\Catalog\Model;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Abstract product value
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractProductValue implements ProductValueInterface
{
    /** @var AttributeInterface */
    protected $attribute;

    /** @var mixed */
    protected $data;

    /**
     * Locale code
     *
     * @var string
     */
    protected $locale;

    /**
     * Scope code
     *
     * @var string
     */
    protected $scope;

    /**
    /**
     * Store many options values
     *
     * This field must by overrided in concret value class
     *
     * @var Collection
     */
    protected $options;

    /** @var array */
    protected $optionIds;

    /**
     * Store upload values
     *
     * @var FileInfoInterface
     */
    protected $media;

    /**
     * @param AttributeInterface $attribute
     * @param string             $channel
     * @param string             $locale
     * @param mixed              $data
     */
    public function __construct(AttributeInterface $attribute, $channel, $locale, $data)
    {
        $this->options = new ArrayCollection();

        $this->setAttribute($attribute);
        $this->setScope($channel);
        $this->setLocale($locale);
        $this->setData($data);
    }

    /**
     * {@inheritdoc}
     */
    public function hasData()
    {
        return !is_null($this->getData());
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute()
    {
        return $this->attribute;
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
    public function getData()
    {
        $getter = $this->attribute->getBackendType();
        if ($this->attribute->isBackendTypeReferenceData()) {
            $getter = $this->attribute->getReferenceDataName();
        }

        $getter = 'get'.ucfirst($getter);

        return $this->$getter();
    }
    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function isEqual(ProductValueInterface $productValue)
    {
        return $this->getData() === $productValue->getData() &&
            $this->scope === $productValue->getScope() &&
            $this->locale === $productValue->getLocale();
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $data = $this->getData();

        if ($data instanceof \DateTimeInterface) {
            $data = $data->format(\DateTime::ISO8601);
        }

        if ($data instanceof Collection || is_array($data)) {
            $items = [];
            foreach ($data as $item) {
                $value = (string) $item;
                if (!empty($value)) {
                    $items[] = $value;
                }
            }

            return implode(', ', $items);
        } elseif (is_object($data)) {
            return (string) $data;
        }

        return (string) $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * Set attribute
     *
     * @param AttributeInterface $attribute
     *
     * @throws \LogicException
     *
     * @return ProductValueInterface
     */
    protected function setAttribute(AttributeInterface $attribute = null)
    {
        if (is_object($this->attribute) && ($attribute != $this->attribute)) {
            throw new \LogicException(
                sprintf('An attribute (%s) has already been set for this value', $this->attribute->getCode())
            );
        }
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Set used scope code
     *
     * @param string $scope
     */
    protected function setScope($scope)
    {
        if ($scope && $this->getAttribute() && $this->getAttribute()->isScopable() === false) {
            $attributeCode = $this->getAttribute()->getCode();
            throw new \LogicException(
                "The product value cannot be scoped, see attribute '".$attributeCode."' configuration"
            );
        }

        $this->scope = $scope;
    }

    /**
     * Set used locale code
     *
     * @param string $locale
     */
    protected function setLocale($locale)
    {
        if ($locale && $this->getAttribute() && $this->getAttribute()->isLocalizable() === false) {
            $attributeCode = $this->getAttribute()->getCode();
            throw new \LogicException(
                "The product value cannot be localized, see attribute '".$attributeCode."' configuration"
            );
        }

        $this->locale = $locale;
    }

    /**
     * Set data
     *
     * @param mixed $data
     *
     * @return ProductValueInterface
     */
    protected function setData($data)
    {
        $setter = $this->attribute->getBackendType();
        if ($this->attribute->isBackendTypeReferenceData()) {
            $setter = $this->attribute->getReferenceDataName();
        }

        $setter = 'set'.ucfirst($setter);

        return $this->$setter($data);
    }
    /**
     * Set options, used for multi select to set many options
     *
     * @param Collection $options An array collection of AttributeOptionInterface
     *
     * @return ProductValueInterface
     */
    protected function setOptions(Collection $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Set media
     *
     * @param FileInfoInterface $media
     *
     * @return ProductValueInterface
     */
    protected function setMedia(FileInfoInterface $media = null)
    {
        $this->media = $media;

        return $this;
    }
}
