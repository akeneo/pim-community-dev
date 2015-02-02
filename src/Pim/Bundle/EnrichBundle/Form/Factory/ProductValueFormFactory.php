<?php

namespace Pim\Bundle\EnrichBundle\Form\Factory;

use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeRegistry;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\EnrichBundle\Event\CreateProductValueFormEvent;
use Pim\Bundle\EnrichBundle\Event\ProductEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Aims to create an product value form
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueFormFactory
{
    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var AttributeTypeRegistry */
    protected $attTypeRegistry;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * Constructor
     *
     * @param FormFactoryInterface     $factory
     * @param AttributeTypeRegistry    $attTypeRegistry
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        FormFactoryInterface $factory,
        AttributeTypeRegistry $attTypeRegistry,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->formFactory = $factory;
        $this->attTypeRegistry = $attTypeRegistry;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param ProductValueInterface $value
     * @param array                 $context
     *
     * @return FormInterface
     */
    public function createProductValueForm(ProductValueInterface $value, array $context)
    {
        $attributeTypeAlias = $value->getAttribute()->getAttributeType();
        $attributeType = $this->attTypeRegistry->get($attributeTypeAlias);

        $name    = $attributeType->prepareValueFormName($value);
        $type    = $attributeType->prepareValueFormAlias($value);
        $data    = $attributeType->prepareValueFormData($value);
        $options = array_merge(
            $attributeType->prepareValueFormConstraints($value),
            $attributeType->prepareValueFormOptions($value)
        );

        $event = new CreateProductValueFormEvent($value, $type, $data, $options, $context);
        $this->eventDispatcher->dispatch(ProductEvents::CREATE_VALUE_FORM, $event);

        $valueForm = $this->formFactory->createNamed(
            $name,
            $event->getFormType(),
            $event->getFormData(),
            $event->getFormOptions()
        );

        return $valueForm;
    }
}
