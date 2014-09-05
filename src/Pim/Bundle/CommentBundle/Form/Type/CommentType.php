<?php

namespace Pim\Bundle\CommentBundle\Form\Type;

use Pim\Bundle\CommentBundle\Repository\CommentRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Comment type
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CommentType extends AbstractType
{
    /** @var CommentRepositoryInterface */
    protected $repository;

    /**
     * @param CommentRepositoryInterface $repository
     */
    public function __construct(CommentRepositoryInterface $repository)
    {
        $this->repository = $repository;    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('body', 'textarea', ['label' => false, 'attr' => ['placeholder' => 'Write something...']])
            ->add('resourceName', 'hidden')
            ->add('resourceId', 'hidden')
        ;

        if (true === $options['is_reply']) {
            $builder->add('parent', 'pim_object_identifier', ['multiple' => false, 'repository' => $this->repository]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Pim\Bundle\CommentBundle\Entity\Comment',
                'is_reply' => false
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_comment_comment';
    }
}
