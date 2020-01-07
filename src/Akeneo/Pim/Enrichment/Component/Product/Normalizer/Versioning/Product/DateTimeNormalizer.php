<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\Product;

use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;

/**
 * Normalize a DateTime
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @see       Pim\Bundle\TransformBundle\Normalizer\Flat\ProductNormalizer
 */
class DateTimeNormalizer extends AbstractValueDataNormalizer implements CacheableSupportsMethodInterface
{
    /** @var string[] */
    protected $supportedFormats = ['csv', 'flat'];

    /** @var string */
    protected $format;

    /**
     * @param string $format see http://www.php.net/date
     */
    public function __construct($format = 'c')
    {
        $this->format = $format;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof \DateTime && in_array($format, $this->supportedFormats);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doNormalize($object, $format = null, array $context = [])
    {
        $context = $this->resolveContext($context);

        return $object->format($context['format']);
    }

    /**
     * Merge default format option with context
     *
     * @param array $context
     *
     * @return array
     */
    protected function resolveContext(array $context = [])
    {
        return array_merge(['format' => $this->format], $context);
    }
}
