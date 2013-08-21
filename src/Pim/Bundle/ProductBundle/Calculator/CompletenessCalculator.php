<?php

namespace Pim\Bundle\ProductBundle\Calculator;

use Pim\Bundle\ProductBundle\Entity\Locale;

use Pim\Bundle\ProductBundle\Entity\Channel;

use Pim\Bundle\ProductBundle\Entity\Completeness;

use Symfony\Component\Validator\Constraints\Collection;

use Doctrine\ORM\EntityManager;

use Pim\Bundle\ProductBundle\Manager\LocaleManager;

use Pim\Bundle\ProductBundle\Manager\ChannelManager;

use Pim\Bundle\ProductBundle\Entity\Product;

/**
 *
 * Enter description here ...
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class CompletenessCalculator
{
    /**
     * @var ChannelManager
     */
    protected $channelManager;

    /**
     * @var LocaleManager
     */
    protected $localeManager;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var array $channels
     */
    protected $channels;

    /**
     * @var array $locales
     */
    protected $locales;

    /**
     * Constructor
     * @param ChannelManager $channelManager
     * @param LocaleManager  $localeManager
     */
    public function __construct(ChannelManager $channelManager, LocaleManager $localeManager, EntityManager $em)
    {
        $this->channelManager = $channelManager;
        $this->localeManager  = $localeManager;
        $this->em             = $em;
    }

    /**
     * Set the channels for which the products must be calculated
     *
     * @param array $channels
     *
     * @return \Pim\Bundle\ProductBundle\Calculator\CompletenessCalculator
     */
    public function setChannels(array $channels = array())
    {
        $this->channels = $channels;

        return $this;
    }

    /**
     * Set the locales for which the products must be calculated
     *
     * @param array $locales
     *
     * @return \Pim\Bundle\ProductBundle\Calculator\CompletenessCalculator
     */
    public function setLocales(array $locales = array())
    {
        $this->locales = $locales;

        return $this;
    }

    /**
     * Get the channels for which the products must be calculated
     *
     * @return array
     */
    protected function getChannels()
    {
        if ($this->channels === null || !is_array($this->channels) || empty($this->channels)) {
            $this->channels = $this->channelManager->getChannels();
        }

        return $this->channels;
    }

    /**
     * Get the locales for which the products must be calculated
     *
     * @return array
     */
    protected function getLocales()
    {
        if ($this->locales === null || !is_array($this->locales) || empty($this->locales)) {
            $this->locales = $this->localeManager->getActiveLocales();
        }

        return $this->locales;
    }

    /**
     * Calculate the completeness of a product list
     *
     * @param array $products
     */
    public function calculate(array $products = array())
    {
        $completenesses = array();

        foreach ($products as $product) {
            $sku = $product->getSku()->__toString();
            $completenesses[$sku] = $this->calculateForAProduct($product);
        }

        return $completenesses;
    }

    /**
     * Calculate the completeness of a product
     *
     * @param Product $product
     * @param array   $completenesses List of completenesses
     *
     * @return $completenesses
     */
    public function calculateForAProduct(Product $product, array $completenesses = array())
    {
        foreach ($this->getChannels() as $channel) {
            $newCompletenesses = $this->calculateForAProductByChannel($product, $channel, $completenesses);

            $completenesses = array_merge($completenesses, $newCompletenesses);
        }

        return $completenesses;
    }

    /**
     * Calculate the completeness of a product for a specific channel
     * @param Product $product
     * @param Channel $channel
     */
    public function calculateForAProductByChannel(Product $product, Channel $channel, array $completenesses = array())
    {
        // get required attributes for this channel
        $requiredAttributes = $this->getRequiredAttributes($channel);
        $countRequiredAttributes = count($requiredAttributes);

        foreach ($this->getLocales() as $locale) {
            $completeness = $product->getCompleteness($locale->getCode(), $channel->getCode());
            if (!$completeness) {
                $completeness = $this->createCompleteness($product, $channel, $locale);
            }

            // initialize counting
            $missingCount = 0;
            $wellCount    = 0;

            foreach ($requiredAttributes as $requiredAttribute) {
                $attribute     = $requiredAttribute->getAttribute();
                $attributeCode = $attribute->getCode();

                $value = $product->getValue($attributeCode, $locale->getCode(), $channel->getCode());

                //TODO : Use NotBlank validator
                if (!$value || $value->getData() === null || $value->getData() === "") {
                    $missingCount++;
                } else {
                    $wellCount++;
                }
            }

            $ratio = ($countRequiredAttributes === 0) ? 100 : $wellCount / $countRequiredAttributes * 100;

            $completeness->setMissingCount($missingCount);
            $completeness->setRatio($ratio);

            $completenesses[] = $completeness;
        }

        return $completenesses;
    }

    /**
     * Create a product completeness
     *
     * @param Product $product
     * @param Channel $channel
     * @param Locale  $locale
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Completeness
     */
    protected function createCompleteness(Product $product, Channel $channel, Locale $locale)
    {
        $completeness = new Completeness();

        $completeness->setProduct($product);
        $completeness->setChannel($channel);
        $completeness->setLocale($locale->getCode());

        return $completeness;
    }

    /**
     * Get the required attributes
     *
     * @param Channel $channel
     *
     * @return array
     */
    protected function getRequiredAttributes(Channel $channel)
    {
        $repo = $this->em->getRepository('PimProductBundle:AttributeRequirement');

        return $repo->findBy(array('channel' => $channel, 'required' => true));
    }
}
