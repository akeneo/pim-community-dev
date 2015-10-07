<?php

namespace Oro\Bundle\TranslationBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;
use Symfony\Bridge\Doctrine\Form\EventListener\MergeDoctrineCollectionListener;
use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;
use Gedmo\Translatable\Query\TreeWalker\TranslationWalker;

use Oro\Bundle\TranslationBundle\Form\DataTransformer\CollectionToArrayTransformer;

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
        if ($options['multiple']) {
            $builder->addEventSubscriber(new MergeDoctrineCollectionListener())
                ->addViewTransformer(new CollectionToArrayTransformer(), true);
        }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
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

            // translation must not be selected separately for each entity
            $entityManager->getConfiguration()->addCustomHydrationMode(
                TranslationWalker::HYDRATE_OBJECT_TRANSLATION,
                'Gedmo\\Translatable\\Hydrator\\ORM\\ObjectHydrator'
            );

            // make entity translatable
            /** @var $queryBuilder QueryBuilder */
            $query = $queryBuilder->getQuery();
            $query->setHint(
                Query::HINT_CUSTOM_OUTPUT_WALKER,
                'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
            );

            $entities = $query->execute(null, TranslationWalker::HYDRATE_OBJECT_TRANSLATION);

            return new ObjectChoiceList($entities, $options['property'], array(), null, $idField);
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
