<?php

namespace Pim\Bundle\EnrichBundle\Form\Type;

use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Bundle\EnrichBundle\Form\DataTransformer\EntityToIdentifierTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Object identifier form type. Provides an hidden field to store
 * single or multiple ids of linked objects
 *
 * @author    Benoit Jacquemont <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ObjectIdentifierType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'hidden';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_object_identifier';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(
            new EntityToIdentifierTransformer(
                $options['repository'],
                $options['multiple'],
                null,
                $options['delimiter'],
                $options['identifier']
            ),
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined([
                'repository_options',
                'identifier',
                'multiple'
            ])
            ->setRequired(['repository'])
            ->setDefaults([
                'repository_options' => [],
                'multiple'           => true,
                'delimiter'          => ',',
                'identifier'         => 'id',
            ])
            ->setAllowedValues('multiple', [true, false])
            ->setAllowedValues([
                'repository' => function ($repository) {
                    return $repository instanceof ObjectRepository;
                }
            ]);
    }
}
