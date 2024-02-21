<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractProductUniqueData implements ProductUniqueDataInterface
{
    /** @var int */
    protected $id;

    /** @var ProductInterface */
    protected $product;

    /** @var AttributeInterface */
    protected $attribute;

    /** @var string */
    protected $rawData;

    public function __construct(ProductInterface $product, AttributeInterface $attribute, string $rawData)
    {
        $this->product = $product;
        $this->attribute = $attribute;
        $this->rawData = $rawData;
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
    public function getProduct(): ProductInterface
    {
        return $this->product;
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
    public function setAttribute(AttributeInterface $attribute): void
    {
        $this->attribute = $attribute;
    }

    /**
     * {@inheritdoc}
     */
    public function setRawData(string $rawData): void
    {
        $this->rawData = $rawData;
    }

    /**
     * {@inheritdoc}
     */
    public function isEqual(ProductUniqueDataInterface $uniqueValue): bool
    {
        return $this->getAttribute() === $uniqueValue->getAttribute() &&
            $this->getRawData() === $uniqueValue->getRawData();
    }
}
