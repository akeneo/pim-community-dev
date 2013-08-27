<?php

namespace Pim\Bundle\ProductBundle\Calculator;

use Symfony\Component\Validator\Validator;
use Doctrine\ORM\EntityManager;
use Pim\Bundle\ProductBundle\Validator\Constraints\ProductValueNotBlank;
use Pim\Bundle\ProductBundle\Manager\LocaleManager;
use Pim\Bundle\ProductBundle\Manager\ChannelManager;
use Pim\Bundle\ProductBundle\Entity\Channel;
use Pim\Bundle\ProductBundle\Entity\Completeness;
use Pim\Bundle\ProductBundle\Entity\Family;
use Pim\Bundle\ProductBundle\Entity\Locale;
use Pim\Bundle\ProductBundle\Entity\Product;

/**
 * Product completeness calculator
 *
 * Purposes different calculations
 * - from a list of products (method calculate)
 * - from a product (method calculateForAProduct)
 * - from a product and a specific channel (method calculateForAProductByChannel)
 *
 * The calculation algorithm get the required attributes for each channel
 * and validate if the value of each required attributes is not blank
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessCalculator
{
    /**
     * @var \Pim\Bundle\ProductBundle\Manager\ChannelManager
     */
    protected $channelManager;

    /**
     * @var \Pim\Bundle\ProductBundle\Manager\LocaleManager
     */
    protected $localeManager;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var Symfony\Component\Validator\Validator
     */
    protected $validator;

    /**
     * @var Channel[]
     */
    protected $channels;

    /**
     * @var Locale[]
     */
    protected $locales;

    /**
     * @var \Pim\Bundle\ProductBundle\Validator\Constraints\ProductValueNotBlank
     */
    protected $notBlankConstraint;

    /**
     * Constructor
     *
     * @param ChannelManager $channelManager
     * @param LocaleManager  $localeManager
     * @param EntityManager  $em
     * @param Validator      $validator
     */
    public function __construct(
        ChannelManager $channelManager,
        LocaleManager $localeManager,
        EntityManager $em,
        Validator $validator
    ) {
        $this->channelManager = $channelManager;
        $this->localeManager  = $localeManager;
        $this->em             = $em;

        $this->validator      = $validator;
        $this->notBlankConstraint = new ProductValueNotBlank();
    }

    /**
     * Set the channels for which the products must be calculated
     *
     * @param Channel[] $channels
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
     * @param Locale[] $locales
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
     * If no locale, all of them are recovered from database
     *
     * @return Channel[]
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
     * If no locale, all of them are recovered from database
     *
     * @return Locale[]
     */
    protected function getLocales()
    {
        if ($this->locales === null || !is_array($this->locales) || empty($this->locales)) {
            $this->locales = $this->localeManager->getActiveLocales();
        }

        return $this->locales;
    }

    /**
     * Calculate the completeness of a products list
     *
     * Returns an associative array of completeness entities like
     * array(
     *     product-sku-1 => array(
     *         completeness entity,
     *         completeness entity
     *     ),
     *     product-sku-2 => array(
     *         completeness entity
     *     )
     * )
     *
     * @param Product[] $products
     *
     * @return Completeness[] $completenesses
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
     *
     * @return Completeness[] $completenesses List of completeness entities for the product
     */
    public function calculateForAProduct(Product $product)
    {
        $completenesses = array();

        foreach ($this->getChannels() as $channel) {
            $newCompletenesses = $this->calculateForAProductByChannel($product, $channel);

            $completenesses = array_merge($completenesses, $newCompletenesses);
        }

        return $completenesses;
    }

    /**
     * Calculate the completeness of a product for a specific channel
     *
     * @param Product        $product
     * @param Channel        $channel
     * @param Completeness[] $completenesses
     *
     * @return Completeness[] $completenesses List of completeness entities
     */
    public function calculateForAProductByChannel(Product $product, Channel $channel, array $completenesses = array())
    {
        $requiredAttributes = $this->getRequiredAttributes($channel, $product->getFamily());
        $requiredCount = count($requiredAttributes);

        foreach ($this->getLocales() as $locale) {
            $completeness = $product->getCompleteness($locale->getCode(), $channel->getCode());
            if (!$completeness) {
                $completeness = $this->createCompleteness($product, $channel, $locale);
            }
            $completeness->setMissingAttributes(array());

            $missingCount = 0;
            $wellCount    = 0;

            foreach ($requiredAttributes as $requiredAttribute) {
                $attribute = $requiredAttribute->getAttribute();
                $value     = $product->getValue($attribute->getCode(), $locale->getCode(), $channel->getCode());

                $errorList = $this->validator->validateValue($value, $this->notBlankConstraint);
                if (count($errorList) === 0) {
                    $wellCount++;
                } else {
                    $missingCount++;
                    $completeness->addMissingAttribute($requiredAttribute->getAttribute());
                }
            }

            $ratio = ($requiredCount === 0) ? 100 : $wellCount / $requiredCount * 100;

            $completeness->setRequiredCount($requiredCount);
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
        $completeness->setLocale($locale);

        return $completeness;
    }

    /**
     * Get the required attributes for a specific channel
     *
     * @param Channel $channel
     * @param Family  $family
     *
     * @return \Pim\Bundle\ProductBundle\Entity\AttributeRequirement[]
     */
    protected function getRequiredAttributes(Channel $channel, Family $family)
    {
        $repo = $this->em->getRepository('PimProductBundle:AttributeRequirement');

        return $repo->findBy(array('channel' => $channel, 'family' => $family, 'required' => true));
    }
}
