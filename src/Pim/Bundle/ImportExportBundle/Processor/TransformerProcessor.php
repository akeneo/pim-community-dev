<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Pim\Bundle\ImportExportBundle\Transformer\ORMTransformer;
use Pim\Bundle\ImportExportBundle\Validator\Import\ImportValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;

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
     * @var ORMTransformer
     */
    protected $transformer;

    /**
     * @var string
     */
    protected $class;

    /**
     * Constructor
     *
     * @param ImportValidatorInterface $validator
     * @param TranslatorInterface      $translator
     * @param ORMTransformer           $transformer
     * @param string                   $class
     * @param boolean                  $skipEmpty
     */
    public function __construct(
        ImportValidatorInterface $validator,
        TranslatorInterface $translator,
        ORMTransformer $transformer,
        $class,
        $skipEmpty = false
    ) {
        parent::__construct($validator, $translator);
        $this->transformer = $transformer;
        $this->class = $class;
        $this->skipEmpty = $skipEmpty;
    }

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
