<?php

namespace Pim\Bundle\CustomEntityBundle\Form;

use Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceFilterType;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Filter type for custom entities
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CustomEntityFilterType extends AbstractType
{
    const NAME = 'pim_custom_entity_filter_entity';

    /**
     * @var RegistryInterface
     */
    protected $doctrine;

    /**
     * Constructor
     *
     * @param RegistryInterface $doctrine
     */
    public function __construct(RegistryInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceFilterType::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(['class']);
        $resolver->setOptional(['sort']);
        $resolver->setDefaults(
            [
                'field_type' => 'choice'
            ]
        );
        $resolver->setNormalizers(
            [
                'field_options' => function (Options $options) {
                    $entities = $this->doctrine
                            ->getRepository($options['class'])
                            ->findBy([], isset($options['sort']) ? $options['sort'] : null);
                    $choices = [];
                    foreach ($entities as $entity) {
                        $choices[$entity->getId()] = (string) $entity;
                    }

                    return [
                        'choices' => $choices
                    ];
                }
            ]
        );
    }
}
