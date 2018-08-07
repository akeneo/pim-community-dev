<?php

namespace Akeneo\Tool\Component\Connector\Processor\Normalization;

use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Simple processor to process and normalize entities to the standard format
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Processor implements ItemProcessorInterface
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ObjectDetacherInterface */
    protected $objectDetacher;

    /**
     * @param NormalizerInterface     $normalizer
     * @param ObjectDetacherInterface $objectDetacher
     */
    public function __construct(NormalizerInterface $normalizer, ObjectDetacherInterface $objectDetacher)
    {
        $this->normalizer = $normalizer;
        $this->objectDetacher = $objectDetacher;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $normalizedItem = $this->normalizer->normalize($item);
        $this->objectDetacher->detach($item);

        return $normalizedItem;
    }
}
