<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Pim\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Symfony\Component\Serializer\Serializer;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SerializerProcessor implements ItemProcessorInterface
{
    protected $serializer;
    protected $format;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function setFormat($format)
    {
        $this->format = $format;
    }

    public function process($item)
    {
        return $this->serializer->serialize($item, $this->format);
    }
}

