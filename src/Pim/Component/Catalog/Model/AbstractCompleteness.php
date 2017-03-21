<?php

namespace Pim\Component\Catalog\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\Entity\Locale;

/**
 * Abstract product completeness entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractCompleteness implements CompletenessInterface
{
    /** @var int|string */
    protected $id;

    /** @var ProductInterface */
    protected $product;

    /** @var LocaleInterface */
    protected $locale;

    /** @var ChannelInterface */
    protected $channel;

    /** @var int */
    protected $ratio = 100;

    /** @var int */
    protected $missingCount = 0;

    /** @var int */
    protected $requiredCount = 0;

    /** @var Collection */
    protected $attributeCompletenesses;

    /**
     * @param ProductInterface $product
     * @param ChannelInterface $channel
     * @param LocaleInterface  $locale
     */
    public function __construct(
        ProductInterface $product,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $this->product = $product;
        $this->channel = $channel;
        $this->locale = $locale;
        $this->attributeCompletenesses = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
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
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * {@inheritdoc}
     */
    public function getRatio()
    {
        return (int) round(100 * ($this->requiredCount - $this->missingCount) / $this->requiredCount);
    }

    /**
     * {@inheritdoc}
     */
    public function getMissingCount()
    {
        $missingAttribute = $this->attributeCompletenesses->filter(
            function (AttributeCompleteness $attributeCompleteness) {
                return !$attributeCompleteness->isComplete();
            }
        );

        return $missingAttribute->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredCount()
    {
        return $this->attributeCompletenesses->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeCompletenesses()
    {
        return $this->attributeCompletenesses;
    }

    public function addRequiredAttribute(AttributeInterface $attribute, $isComplete)
    {
        $this->attributeCompletenesses->add(new AttributeCompleteness($this, $attribute, $isComplete));
    }
}
