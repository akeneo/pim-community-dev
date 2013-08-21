<?php

namespace Pim\Bundle\ProductBundle\Calculator;

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
     * Calculate the completeness of a product
     * @param Product $product
     */
    public function calculate(Product $product, $channels = null, $locales = null)
    {
        if ($channels === null) {
            $channels = $this->channelManager->getChannels();
        }

        if ($locales === null) {
            $locales = $this->localeManager->getActiveLocales();
        }

        foreach ($channels as $channel) {

            $channelCode = $channel->getCode();

            // get required attributes for this channel
            $repo = $this->em->getRepository('PimProductBundle:AttributeRequirement');
            $requiredAttributes = $repo->findBy(array('channel' => $channel, 'required' => true));
            $countRequiredAttributes = count($requiredAttributes);
            if ($countRequiredAttributes === 0) {
                continue;
            }

            foreach ($locales as $locale) {

                $localeCode = $locale->getCode();

                // Create product completeness entity
                $completeness = $product->getCompleteness($localeCode, $channelCode);
                if (!$completeness) {
                    $completeness = $this->createCompleteness($product, $channel, $localeCode);
                }

                // initialize counting
                $missingCount = 0;
                $wellCount    = 0;

                foreach ($requiredAttributes as $requiredAttribute) {
                    $attribute     = $requiredAttribute->getAttribute();
                    $attributeCode = $attribute->getCode();

                    $value = $product->getValue($attributeCode, $localeCode, $channelCode);

                    //TODO : Use NotBlank validator
                    if (!$value || $value->getData() === null || $value->getData() === "") {
                        $missingCount++;
                    } else {
                        $wellCount++;
                    }
                }

                $ratio = $wellCount / $countRequiredAttributes * 100;

                $completeness->setMissingCount($missingCount);
                $completeness->setRatio($ratio);

                $this->em->persist($completeness);
            }
        }

        $this->em->flush();
    }

    /**
     * Create a product completeness
     *
     * @param Product $product
     * @param Channel $channel
     * @param string $localeCode
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Completeness
     */
    protected function createCompleteness(Product $product, Channel $channel, $localeCode)
    {
        $completeness = new Completeness();

        $completeness->setProduct($product);
        $completeness->setChannel($channel);
        $completeness->setLocale($localeCode);

        return $completeness;
    }
}
