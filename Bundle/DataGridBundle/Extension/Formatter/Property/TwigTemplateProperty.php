<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter\Property;

use Oro\Bundle\DataGridBundle\Extension\Formatter\ResultRecordInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;

class TwigTemplateProperty extends AbstractProperty implements TwigPropertyInterface
{
    /**
     * @var \Twig_Environment
     */
    protected $environment;

    /**
     * @var string
     */
    protected $templateName;

    /**
     * @var \Twig_TemplateInterface
     */
    protected $template;

    /**
     * @var FieldDescriptionInterface
     */
    protected $field;

    /**
     * @var array
     */
    protected $context;

    /**
     * @var array
     */
    protected $reservedKeys = array(
        'field',
        'record',
        'value',
    );


    /**
     * @param FieldDescriptionInterface $field
     * @param string                    $templateName
     * @param array                     $context
     * @throws \InvalidArgumentException
     */
    public function __construct(FieldDescriptionInterface $field, $templateName, $context = array())
    {
        $this->field        = $field;
        $this->templateName = $templateName;
        $this->context      = $context;

        $checkInvalidArgument = array_intersect_key($context, array_flip($this->reservedKeys));
        if (count($checkInvalidArgument)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Context of template "%s" includes reserved key(s) - (%s)',
                    $this->templateName,
                    implode(', ', array_keys($checkInvalidArgument))
                )
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->field->getName();
    }

    /**
     * @param \Twig_Environment $environment
     */
    public function setEnvironment(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * @return \Twig_TemplateInterface
     */
    protected function getTemplate()
    {
        if (!$this->template) {
            $this->template = $this->environment->loadTemplate($this->templateName);
        }

        return $this->template;
    }

    /**
     * Render field template
     * @param ResultRecordInterface $record
     * @return string
     */
    public function getValue(ResultRecordInterface $record)
    {
        $context = array_merge(
            $this->context,
            array(
                'field'  => $this->field,
                'record' => $record,
                'value'  => $record->getValue($this->field->getFieldName()),
            )
        );

        return $this->getTemplate()->render($context);
    }
}
