<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Pim\Bundle\ImportExportBundle\Transformer\OrmTransformer;

/**
 * Description of TransformerProcessor
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TransformerProcessor extends AbstractTransformerProcessor
{
    /**
     * @var OrmTransformer
     */
    protected $transformer;

    /**
     * @var string
     */
    protected $class;

    /**
     * {@inheritdoc}
     */
    protected function transform($item)
    {
        return $this->transformer->transform($this->class, $item);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTransformedColumnsInfo()
    {
        return $this->transformer->getTransformedColumnsInfo();
    }

    /**
     * {@inheritdoc}
     */
    protected function getTransformerErrors()
    {
        return $this->transformer->getErrors();
    }
}
