<?php

namespace Oro\Bundle\GridBundle\Property;

use Oro\Bundle\GridBundle\Datagrid\ResultRecordInterface;
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
    protected $properties;

    /**
     * @param FieldDescriptionInterface $field
     * @param string $templateName
     */
    public function __construct(FieldDescriptionInterface $field, $templateName, $properties = array())
    {
        $this->field        = $field;
        $this->templateName = $templateName;
        $this->properties   = $properties;
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
     *
     * @param ResultRecordInterface $record
     * @return string
     */
    public function getValue(ResultRecordInterface $record)
    {
        $context = array(
            'field'      => $this->field,
            'record'     => $record,
            'value'      => $record->getValue($this->field->getFieldName()),
            'properties' => $this->properties,
        );

        return $this->getTemplate()->render($context);
    }
}
