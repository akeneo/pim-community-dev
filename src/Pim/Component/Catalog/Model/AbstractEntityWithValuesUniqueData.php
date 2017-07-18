<?php

namespace Pim\Component\Catalog\Model;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractEntityWithValuesUniqueData implements EntityWithValuesUniqueDataInterface
{
    /** @var int */
    protected $id;

    /** @var ProductInterface */
    protected $entityWithValues;

    /** @var ValueInterface */
    protected $value;

    /** @var AttributeInterface */
    protected $attribute;

    /** @var mixed */
    protected $rawData;

    /**
     * @param ProductInterface $entityWithValues
     * @param ValueInterface   $value
     */
    public function __construct(ProductInterface $entityWithValues, ValueInterface $value)
    {
        $this->entityWithValues = $entityWithValues;
        $this->setValue($value);
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityWithValues(): EntityWithValuesInterface
    {
        return $this->entityWithValues;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute(): AttributeInterface
    {
        return $this->attribute;
    }

    /**
     * {@inheritdoc}
     */
    public function getRawData(): string
    {
        return $this->rawData;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue(ValueInterface $value): void
    {
        $this->value = $value;
        $this->attribute = $value->getAttribute();
        $this->rawData = $value->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function isEqual(EntityWithValuesUniqueDataInterface $uniqueValue): bool
    {
        return $this->getAttribute() === $uniqueValue->getAttribute() &&
            $this->getRawData() === $uniqueValue->getRawData();
    }
}
