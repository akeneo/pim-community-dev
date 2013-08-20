<?php

namespace Pim\Bundle\ProductBundle\Calculator;

use Pim\Bundle\ProductBundle\Entity\ProductCompleteness;

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
class ProductCompletenessCalculator
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
    public function calculate(Product $product)
    {
        foreach ($this->getChannels() as $channel) {
            // get required attributes for this channel
            $repo = $this->em->getRepository('PimProductBundle:AttributeRequirement');
            $requiredAttributes = $repo->findBy(array('channel' => $channel, 'required' => true));
            $countRequiredAttributes = count($requiredAttributes);

            echo "<hr />CHANNEL CODE --> ". $channel->getCode() ."<br />";
            echo $countRequiredAttributes ." required attributes<br />";

            foreach ($this->getActiveLocales() as $localeCode) {

                $completeness = $this->createCompleteness();
                $completeness->setProduct($product);
                $completeness->setChannel($channel);
                $completeness->setLocale($localeCode);



                echo "LOCALE CODE --> ". $localeCode ."<br />";
                $wellCount    = 0;
                $missingCount = 0;

                foreach ($requiredAttributes as $requiredAttribute) {

                    $attribute     = $requiredAttribute->getAttribute();
                    $scopable      = $attribute->getScopable() ? true : null;
                    $localizable   = $attribute->getTranslatable() ? true : null;
                    $attributeCode = $attribute->getCode();

                    $value = $product->getValue($attributeCode, $localeCode, $channel->getCode());

                    //TODO : Use NotBlank validator
                    if (!$value || $value->getData() === null || $value->getData() === "") {
                        $missingCount++;
                    } else {
                        $wellCount++;
                    }
                }

                echo $wellCount ." well filled values<br />";
                echo $missingCount ." missing values<br />";
                $ratio = $wellCount / $countRequiredAttributes * 100;
                echo "Calculating the ratio... : ". $ratio ."%<br />";


                $completeness->setMissingCount($missingCount);

                echo "<br />";
            }

//             $product->getValue($attributeCode, $localeCode, $scopeCode);

            var_dump($channel->getCode());
            var_dump(count($requiredAttributes));
            echo "<hr />";
        }

        die;
    }

    protected function getChannels()
    {
        return $this->channelManager->getChannels();
    }

    protected function getActiveLocales()
    {
        return $this->localeManager->getActiveCodes();
    }

    /**
     * Create a product completeness
     * @return \Pim\Bundle\ProductBundle\Entity\ProductCompleteness
     */
    protected function createCompleteness()
    {
        return new ProductCompleteness();
    }
}
