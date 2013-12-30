<?php

namespace Pim\Bundle\JSONConnectorBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Entity\Channel;

/**
 * Class ProductNormalizer transform a product entity into an array
 * 
 * @copyright 2014 Sylvain Rascar <srascar@webnet.fr>
 * @author Sylvain Rascar <srascar@webnet.fr>
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNormalizer implements NormalizerInterface
{

    /**
     * @var Channel
     */
    protected $channel;

    /**
     * @var array Locale codes
     */
    protected $locales;

    /**
     * @var array
     */
    protected $supportedFormats = array('json');

    const DATE_FORMAT = 'Y-m-d H:i:s';

    
    /**
     * {@inheritdoc}
     */
    public function normalize(ProductInterface $product, $format = null, array $context = array())
    {
        $this->handleContext($context);
        $values = $this->filterValues($product->getValues());
        $identifier = $product->getIdentifier();
        
        $normalizedValues = $values->map(
            function ($value) {
                return $this->getNormalizedValue($value);
            }
        );
        
        $normalizedRelatedEntities = $this->getNormalizedRelatedEntities($product);
        
        $normalizedProduct = array_merge(
            array('id' => $identifier->getData()), 
            $normalizedValues, 
            $normalizedRelatedEntities
        );
        
        return $normalizedProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * Returns a subset of values that match context the channel
     *
     * @param ArrayCollection $values
     *
     * @return ArrayCollection
     */
    protected function filterValues($values)
    {
        if ($this->channel) {
            $channelCode = $this->channel->getCode();
            $values = $values->filter(
                    function ($value) use ($channelCode) {
                return (!$value->getAttribute()->isScopable() || $value->getScope() == $channelCode);
            }
            );
        }
        if ($this->locales) {
            $localeCodes = $this->locales;
            $values = $values->filter(
                    function ($value) use ($localeCodes) {
                return (!$value->getAttribute()->isTranslatable() || in_array($value->getLocale(), $localeCodes));
            }
            );
        }

        return $values;
    }

    /**
     * get normalized attribute
     *
     * @param mixed $value Value to normalize
     *
     * @return mixed $value Normalized $value
     */
    protected function getNormalizedValue($value)
    {
        $data = array();
        $code = $value->getAttribute()->getCode();
        $valueData = $this->normalizeValueData($value->getData());
        
        if ($value->getAttribute()->isTranslatable() && ($this->locales || $value->getLocale())) {
            $valueLocales = $value->getLocale() ? array($value->getLocale()) : $this->locales;
            foreach ($valueLocales as $valueLocale) {
                $data[$code][$valueLocale] = $valueData;
            }
            // if attribute is not translatable we don't set a locale code for the value
        } else {
            $data[$code] = $valueData;
        }

        return $data;
    }

    /**
     * Prepares value data form serialization
     *
     * @param mixed $data Data to normalize
     *
     * @return mixed $data Normalized data
     */
    protected function normalizeValueData($data)
    {
        if ($data instanceof \Doctrine\Common\Collections\Collection) {
            $items = array();
            foreach ($data as $item) {
                $items[] = (string) $item;
            }

            return implode(', ', $items);
        }

        if (method_exists($data, '__toString')) {
            return (string) $data;
        }
        if ($data instanceof \DateTime) {
            return $data->format('c');
        }

        return $data;
    }

    /**
     * Get an array of available locale codes
     * according to the channel if defined
     *
     * @param array $context normalize context
     *
     * @return array
     */
    protected function handleContext($context)
    {
        $this->channel = isset($context['channel']) && $context['channel'] instanceof Channel ?
                $context['channel'] :
                null;

        /**
         * @TODO check locales validity
         * Maybe a regex /^[a-z]{2}_[A-Z]{2}$/
         */
        $localeCodes = isset($context['locales']) && is_array($context['locales']) ?
                $context['locales'] :
                null;
        
        $this->locales = $this->channel ? $localeCodes : $this->channel->getLocales()->map(
                function ($locale) use ($localeCodes) {
                    if (in_array($locale->getCode(), $localeCodes)) {
                        return $locale->getCode();
                    } else {
                        throw new \LogicException(sprintf(
                                'The locale %s is not available for this channel', $locale
                        ));
                    }
                }
            )->toArray();
    }

    /**
     * get family code, created, updated, and product's categories
     * 
     * @param ProductInterface $product
     * 
     * @return array
     */
    protected function getNormalizedRelatedEntities($product)
    {
        
        return array(
            'family' => $product->getFamily()->getCode(),
            'categories' => $this->getProductCategoriesCodes($product),
            'created' => $product->getCreated()->format(self::DATE_FORMAT),
            'updated' => $product->getUpdated()->format(self::DATE_FORMAT),
            
        );
    }

    /**
     * get product's categories codes
     * 
     * @param ProductInterface $product
     * 
     * @return String
     */
    protected function getProductCategoriesCodes($product)
    {
        $categories = $product->getCategories()->map(
            function ($category) {
                return $category->getCode();
            }
        );
        
        return implode(', ', $categories);
    }
}
