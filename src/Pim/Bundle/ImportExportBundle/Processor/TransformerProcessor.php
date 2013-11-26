<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

/**
 * Description of TransformerProcessor
 *
 * @author Antoine Guigan <aguigan@qimnet.com>
 */
class TransformerProcessor extends AbstractTransformerProcessor
{
    /**
     * @var \Pim\Bundle\ImportExportBundle\Transformer\OrmTransformer
     */
    protected $transformer;

    /**
     * @var string
     */
    protected $class;

    protected function transform($item)
    {
        return $this->transformer->transform($this->class, $item);
    }

    public function getConfigurationFields()
    {

    }
}
