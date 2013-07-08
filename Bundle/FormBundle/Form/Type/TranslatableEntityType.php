<?php

namespace Oro\Bundle\FormBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;
use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;

use Oro\Bundle\FormBundle\Form\DataTransformer\EntitiesToIdsTransformer;
use Oro\Bundle\FormBundle\Form\DataTransformer\EntityToIdTransformer;

class TranslatableEntityType extends AbstractType
{
    const NAME = 'translatable_entity';

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
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // transformer must be only one in chain
        $builder->resetViewTransformers();

        /** @var $entityManager EntityManager */
        $entityManager = $this->registry->getManager();
        if (!empty($options['multiple'])) {
            $builder->addViewTransformer(new EntitiesToIdsTransformer($entityManager, $options['class']));
        } else {
            $builder->addViewTransformer(new EntityToIdTransformer($entityManager, $options['class']));
        }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $registry = $this->registry;

        $choiceList = function (Options $options) use ($registry) {
            $className = $options['class'];

            /** @var $entityManager EntityManager */
            $entityManager = $registry->getManager();
            $idField = $entityManager->getClassMetadata($className)->getSingleIdentifierFieldName();

            if (null !== $options['choices']) {
                return new ObjectChoiceList($options['choices'], $options['property'], array(), null, $idField);
            }

            // get query builder
            if (!empty($options['query_builder'])) {
                $queryBuilder = $options['query_builder'];
                if ($queryBuilder instanceof \Closure) {
                    $queryBuilder = $queryBuilder($registry->getRepository($className));
                }
            } else {
                /** @var $repository EntityRepository */
                $repository = $registry->getRepository($className);
                $queryBuilder = $repository->createQueryBuilder('e');
            }

            // make entity translatable
            /** @var $queryBuilder QueryBuilder */
            $query = $queryBuilder->getQuery();
            $query->setHint(
                Query::HINT_CUSTOM_OUTPUT_WALKER,
                'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
            );

            return new ObjectChoiceList($query->execute(), $options['property'], array(), null, $idField);
        };

        $resolver->setDefaults(
            array(
                'property'      => null,
                'query_builder' => null,
                    'choices'       => null,
                'choice_list'   => $choiceList
            )
        );

        $resolver->setRequired(array('class'));
    }
}
