<?php
namespace Pim\Bundle\ImportExportBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\ProductBundle\Model\ProductInterface;
use Pim\Bundle\ConfigBundle\Entity\Channel;

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
     * Constructor
     *
     * @param Router $router
     */
    public function __construct($router)
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
    public function setlocale($locale = null)
    {
        if ($this->channel) {
            $locales = $this->channel->getLocales()->map(
                function ($locale) {
                    return $locale->getCode();
                }
            )->toArray();

            if (in_array($locale, $locales) || $locale === null) {
                $this->locale = $locale;

                return $this;
            }
        }

        throw new \LogicException('This locale is not available for this channel');
    }

    /**
     * Normalizes a product into a set of arrays/scalars
     *
     * @param ProductInterface $product Product entity to normalize
     * @param string           $format  Encoding format
     *
     * @return array|scalar
     */
    public function normalize($product, $format = null)
    {
        if (!$this->channel) {
            throw new \LogicException('You must specify a channel to return the product for');
        }

        $channelCode = $this->channel->getCode();

        $values = $product->getValues();

        $values = $values->filter(
            function ($value) use ($channelCode) {
                return (!$value->getAttribute()->getScopable() || $value->getScope() == $channelCode);
            }
        );

        $locales = $this->locale ? array($this->locale) : $this->channel->getLocales()->map(
            function ($locale) {
                return $locale->getCode();
            }
        )->toArray();

        $values = $values->filter(
            function ($value) use ($locales) {
                return (!$value->getAttribute()->getTranslatable() || in_array($value->getLocale(), $locales));
            }
        );

        $attributes = $values->map(
            function ($value) {
                return $value->getAttribute();
            }
        );
        $attributes = array_unique($attributes->toArray());

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

        $data['resource'] = $this->router->generate(
            'oro_api_get_product',
            array(
                'scope' => $this->channel->getCode(),
                'identifier' => $identifier
            ),
            true
        );

        return array($identifier => $data);
    }

    /**
     * Checks whether the given class is supported for normalization by this normalizer
     *
     * @param mixed  $data   Data to normalize
     * @param string $format Serialization format
     *
     * @return boolean
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface;
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
