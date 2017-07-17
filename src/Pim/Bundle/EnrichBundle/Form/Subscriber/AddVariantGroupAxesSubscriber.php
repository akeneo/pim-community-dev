<?php

namespace Pim\Bundle\EnrichBundle\Form\Subscriber;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Adds axes to the variant group form
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddVariantGroupAxesSubscriber implements EventSubscriberInterface
{
    /** @var string */
    protected $attributeClass;

    /**
     * @param string $attributeClass
     */
    public function __construct($attributeClass)
    {
        $this->attributeClass = $attributeClass;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function preSetData(FormEvent $event)
    {
        $group = $event->getData();
        $options = [
            'label'    => 'Axis',
            'required' => true,
            'multiple' => true,
            'class'    => $this->attributeClass,
            'help'     => 'pim_enrich.group.axis.help',
            'select2'  => true,
        ];

        if (null !== $group &&
            null !== $group->getId() &&
            null !== $group->getType() &&
            $group->getType()->isVariant()
        ) {
            $extraOptions = $this->getFormOptionsForEdition($group);
        } else {
            $extraOptions = $this->getFormOptionsForCreation();
        }

        $form = $event->getForm();
        $form->add(
            'axisAttributes',
            'entity',
            array_merge($options, $extraOptions)
        );
    }

    /**
     * @param GroupInterface $group
     *
     * @return array
     */
    protected function getFormOptionsForEdition(GroupInterface $group)
    {
        $axesIds = array_map(
            function (AttributeInterface $attribute) {
                return $attribute->getId();
            },
            $group->getAxisAttributes()->toArray()
        );

        $options = [
            'data'      => $group->getAxisAttributes(),
            'disabled'  => true,
            'attr'      => [
                'read_only' => true,
            ],
            // only define a qb with the axes we want to avoid useless lazy loading on attribute translations
            'query_builder' => function (AttributeRepositoryInterface $repository) use ($axesIds) {
                $qb = $repository->findAllAxesQB();
                $qb->andWhere('a.id IN (:ids)');
                $qb->setParameter('ids', $axesIds);

                return $qb;
            },
        ];

        return $options;
    }

    /**
     * @return array
     */
    protected function getFormOptionsForCreation()
    {
        $options = [
            'query_builder' => function (AttributeRepositoryInterface $repository) {
                return $repository->findAllAxesQB();
            },
        ];

        return $options;
    }
}
