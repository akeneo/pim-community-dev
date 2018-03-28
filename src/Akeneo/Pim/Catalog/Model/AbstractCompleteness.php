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
    protected $ratio;

    /** @var int */
    protected $missingCount;

    /** @var int */
    protected $requiredCount;

    /** @var Collection */
    protected $missingAttributes;

    /**
     * @param ProductInterface $product
     * @param ChannelInterface $channel
     * @param LocaleInterface  $locale
     * @param Collection       $missingAttributes
     * @param int              $missingCount
     * @param int              $requiredCount
     */
    public function __construct(
        ProductInterface $product,
        ChannelInterface $channel,
        LocaleInterface $locale,
        Collection $missingAttributes,
        $missingCount,
        $requiredCount
    ) {
        $this->product = $product;
        $this->channel = $channel;
        $this->locale = $locale;
        $this->missingAttributes = $missingAttributes;
        $this->missingCount = $missingCount;
        $this->requiredCount = $requiredCount;

        $this->ratio = (int) floor(100 * ($this->requiredCount - $this->missingCount) / $this->requiredCount);
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
        return $this->ratio;
    }

    /**
     * {@inheritdoc}
     */
    public function getMissingCount()
    {
        return $this->missingCount;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredCount()
    {
        return $this->requiredCount;
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
    public function getMissingAttributes()
    {
        return $this->missingAttributes;
    }
}
