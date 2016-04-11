<?php

namespace Pim\Component\Connector\Processor\Normalization;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Simple processor to process and normalize entities to the given format
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SimpleProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var string */
    protected $format;

    /**
     * @param NormalizerInterface $normalizer
     * @param string              $format
     */
    public function __construct(NormalizerInterface $normalizer, $format)
    {
        $this->normalizer = $normalizer;
        $this->format     = $format;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException if the given format is not a string
     */
    public function process($item)
    {
        return $this->normalizer->normalize($item, $this->format);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [];
    }
}
