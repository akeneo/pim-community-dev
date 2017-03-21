<?php

namespace Pim\Bundle\EnrichBundle\Form\Type;

use Pim\Bundle\EnrichBundle\Form\DataTransformer\EntityToIdentifierTransformer;
use Pim\Component\Enrich\Provider\TranslatedLabelsProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            new EntityToIdentifierTransformer(
                $options['repository'],
                $options['multiple'],
                null,
                null,
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
            ])
            ->setRequired(['repository'])
            ->setDefaults([
                'repository_options' => [],
                'identifier'         => 'code',
            ])
            ->setNormalizer('choices', function (Options $options, $value) {
                return $options['repository']->findTranslatedLabels($options['repository_options']);
            })
            ->setAllowedValues([
                'repository' => function ($repository) {
                    return $repository instanceof TranslatedLabelsProviderInterface;
                }
            ]);
    }
}
