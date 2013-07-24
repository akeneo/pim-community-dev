<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Pim\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Symfony\Component\Serializer\Serializer;
use Pim\Bundle\ImportExportBundle\AbstractConfigurableStepElement;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SerializerProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface
{
    protected $serializer;
    protected $format;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function getFormat()
    {
        return $this->format;
    }

    public function setFormat($format)
    {
        $this->format = $format;
    }

    public function process($item)
    {
        return $this->serializer->serialize($item, $this->format);
    }

    public function getConfigurationFields()
    {
        return array(
            'format' => array(
                'type'    => 'choice',
                'options' => array(
                    'choices' => array(
                        'csv'  => 'CSV',
                        'xml'  => 'XML',
                        'json' => 'JSON',
                    ),
                ),
            )
        );
    }
}

