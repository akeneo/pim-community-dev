<?php

namespace Oro\Bundle\ImportExportBundle\Processor;

use Oro\Bundle\ImportExportBundle\Context\ContextAwareInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Processor\ContextAwareProcessor;
use Oro\Bundle\ImportExportBundle\Converter\DataConverterInterface;
use Oro\Bundle\ImportExportBundle\Strategy\StrategyInterface;

class ImportProcessor implements ProcessorInterface, ContextAwareProcessor, SerializerAwareInterface
{
    /**
     * @var ContextInterface
     */
    protected $context;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var DataConverterInterface
     */
    protected $converter;

    /**
     * @var StrategyInterface
     */
    protected $strategy;

    /**
     * @var Validator
     */
    protected $validator;

    /**
     * {@inheritdoc}
     */
    public function setImportExportContext(ContextInterface $context)
    {
        $this->context = $context;

        if ($this->strategy && $this->strategy instanceof ContextAwareInterface) {
            $this->strategy->setImportExportContext($this->context);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param DataConverterInterface $converter
     */
    public function setDataConverter(DataConverterInterface $converter)
    {
        $this->converter = $converter;
    }

    /**
     * @param StrategyInterface $strategy
     */
    public function setStrategy(StrategyInterface $strategy)
    {
        $this->strategy = $strategy;
    }

    /**
     * @param Validator $validator
     */
    public function setValidator($validator)
    {
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        if ($this->converter) {
            $item = $this->converter->convertToImportFormat($item);
        }

        $object = $this->serializer->deserialize($item, $this->context->getOption('entityName'), null);

        if ($this->strategy) {
            $object = $this->strategy->process($object);
        }

        return $this->isValid($object) ? $object : null;
    }

    /**
     * Validate object.
     *
     * @param object $object
     * @return bool
     */
    protected function isValid($object)
    {
        if ($this->validator) {
            $violations = $this->validator->validate($object);
            if (count($violations)) {
                /** @var ConstraintViolationInterface $violation */
                foreach ($violations as $violation) {
                    $this->context->addError($violation->getMessage());
                }
                return false;
            }
        }

        return true;
    }
}
