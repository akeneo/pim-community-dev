<?php

namespace Pim\Bundle\EnrichBundle\Form\Type;

use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Pim\Bundle\EnrichBundle\Form\DataTransformer\ChoicesProviderInterface;
use Pim\Bundle\EnrichBundle\Form\DataTransformer\EntityToIdentifierTransformer;

/**
 * Light entity form type
 * It prevents hydrating all the entity choices
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LightEntityType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'light_entity';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(
            new EntityToIdentifierTransformer($options['repository'], $options['multiple']),
            true
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(['repository_options' => []])
            ->setRequired(['repository'])
            ->setNormalizers(
                [
                    'choices' => function (Options $options, $value) {
                        return $options['repository']->getChoices($options['repository_options']);
                    },
                    'repository' => function (Options $options, $value) {
                        if (!$value instanceof ObjectRepository) {
                            throw new UnexpectedTypeException(
                                '\Doctrine\Common\Persistence\ObjectRepository',
                                $value
                            );
                        }
                        if (!$value instanceof ChoicesProviderInterface) {
                            throw new UnexpectedTypeException(
                                '\Pim\Bundle\EnrichBundle\Form\DataTransformer\ChoicesProviderInterface',
                                $value
                            );
                        }

                        return $value;
                    }
                ]
            );
    }
}
