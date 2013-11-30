<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Routing\Router;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Entity\Channel;

/**
 * A normalizer to transform a product entity into an array
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNormalizer implements NormalizerInterface
{
    /**
     * @var array
     */
    protected $supportedFormats = array('json', 'xml');

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var Channel
     */
    protected $channel;

    /**
     * @var string Locale code
     */
    protected $locale;

    /**
     * TODO : make that normalizer useable without channel and router (use context ?)
     *
     * Constructor
     *
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Set channel to return product data for
     *
     * @param string $channel Channel code
     *
     * @return ProductNormalizer
     */
    public function setChannel(Channel $channel = null)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * Set locale to return product data for
     *
     * @param string $locale Locale code
     *
     * @return ProductNormalizer
     */
    public function setLocale($locale = null)
    {
        if ($this->channel) {
            $localeCodes = $this->getLocales();

            if (in_array($locale, $localeCodes) || $locale === null) {
                $this->locale = $locale;

                return $this;
            }
        }

        throw new \LogicException('This locale is not available for this channel');
    }

    /**
     * Get an array of available locale codes
     * @return array
     */
    public function getLocales()
    {
        return $this->locale ? array($this->locale) : $this->channel->getLocales()->map(
            function ($locale) {
                return $locale->getCode();
            }
        )->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = array())
    {
        $values = $this->filterValues($product->getValues());
        $attributes = $this->getAttributes($values);
        $locales = $this->getLocales();

        $data = array();

        foreach ($attributes as $attribute) {
            $code = $attribute->getCode();

            $attributeValues = $values->filter(
                function ($value) use ($code) {
                    return $value->getAttribute()->getCode() == $code;
                }
            );

            foreach ($attributeValues as $value) {
                $valueLocales = $value->getLocale() ? array($value->getLocale()): $locales;
                $valueData = $this->normalizeValueData($value->getData());
                foreach ($valueLocales as $valueLocale) {
                    $data[$code][$valueLocale] = $valueData;
                }
            }
        }

        $identifier = $product->getIdentifier();

        if ($this->router) {
            $data['resource'] = $this->router->generate(
                'oro_api_get_product',
                array(
                    'scope' => $this->channel->getCode(),
                    'identifier' => $identifier->getData()
                ),
                true
            );
        }

        return array($identifier->getData() => $data);
    }

    /**
     * Returns a subset of values that match the channel and locale requirements
     *
     * @param ArrayCollection $values
     *
     * @return ArrayCollection
     */
    public function filterValues($values)
    {
        if (!$this->channel) {
            throw new \LogicException('You must specify a channel to return the product for');
        }

        $channelCode = $this->channel->getCode();

        $values = $values->filter(
            function ($value) use ($channelCode) {
                return (!$value->getAttribute()->isScopable() || $value->getScope() == $channelCode);
            }
        );

        $localeCodes = $this->getLocales();

        $values = $values->filter(
            function ($value) use ($localeCodes) {
                return (!$value->getAttribute()->getTranslatable() || in_array($value->getLocale(), $localeCodes));
            }
        );

        return $values;
    }

    /**
     * Returns an array of all attrbutes for the provided values
     *
     * @param ArrayCollection $values
     *
     * @return array
     */
    public function getAttributes($values)
    {
        $attributes = $values->map(
            function ($value) {
                return $value->getAttribute();
            }
        );

        $uniqueAttributes = array();
        foreach ($attributes as $attribute) {
            if (!array_key_exists($attribute->getCode(), $uniqueAttributes)) {
                $uniqueAttributes[$attribute->getCode()] = $attribute;
            }
        }

        return $uniqueAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface && in_array($format, $this->supportedFormats);
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
}
