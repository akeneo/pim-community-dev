<?php

namespace Akeneo\Platform\Bundle\UIBundle\Form\Type;

use Akeneo\Pim\Enrichment\Bundle\Form\Subscriber\FixArrayToStringListener;
use Akeneo\Platform\Bundle\UIBundle\Form\Exception\FormException;
use Akeneo\Platform\Bundle\UIBundle\Form\Transformer\ArrayToStringTransformer;
use Akeneo\Platform\Bundle\UIBundle\Form\Transformer\EntitiesToIdsTransformer;
use Akeneo\Platform\Bundle\UIBundle\Form\Transformer\EntityToIdTransformer;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntityIdentifierType extends AbstractType
{
    const NAME = 'pim_enrich_entity_identifier';

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addViewTransformer($this->createEntitiesToIdsTransformer($options));
        if ($options['multiple']) {
            $builder->addViewTransformer(new ArrayToStringTransformer($options['values_delimiter'], true))
                ->addEventSubscriber(new FixArrayToStringListener($options['values_delimiter']));
        }
    }

    /**
     * @param array $options
     * @return EntitiesToIdsTransformer
     */
    protected function createEntitiesToIdsTransformer(array $options)
    {
        if ($options['multiple']) {
            return new EntitiesToIdsTransformer(
                $options['em'],
                $options['class'],
                $options['property'],
                $options['queryBuilder']
            );
        } else {
            return new EntityToIdTransformer(
                $options['em'],
                $options['class'],
                $options['property'],
                $options['queryBuilder']
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'em'               => null,
                'property'         => null,
                'queryBuilder'     => null,
                'multiple'         => true,
                'values_delimiter' => ','
            ]
        )
        ->setAllowedValues('multiple', [true, false])
        ->setRequired(['class']);

        $registry = $this->registry;

        $resolver
            ->setNormalizer('em', function (Options $options, $em) use ($registry) {
                if (null !== $em) {
                    if ($em instanceof EntityManager) {
                        return $em;
                    } elseif (is_string($em)) {
                        $em = $registry->getManager($em);
                    } else {
                        throw new FormException(
                            sprintf(
                                'Option "em" should be a string or entity manager object, %s given',
                                is_object($em) ? get_class($em) : gettype($em)
                            )
                        );
                    }
                } else {
                    $em = $registry->getManagerForClass($options['class']);
                }

                if (null === $em) {
                    throw new FormException(
                        sprintf(
                            'Class "%s" is not a managed Doctrine entity. Did you forget to map it?',
                            $options['class']
                        )
                    );
                }

                return $em;
            })
            ->setNormalizer('queryBuilder', function (Options $options, $queryBuilder) {
                if (null !== $queryBuilder && !is_callable($queryBuilder)) {
                    throw new FormException(
                        sprintf(
                            'Option "queryBuilder" should be a callable, %s given',
                            is_object($queryBuilder) ? get_class($queryBuilder) : gettype($queryBuilder)
                        )
                    );
                }

                return $queryBuilder;
            });
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return HiddenType::class;
    }
}
