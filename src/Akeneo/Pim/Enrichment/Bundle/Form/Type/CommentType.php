<?php

namespace Akeneo\Pim\Enrichment\Bundle\Form\Type;

use Akeneo\Pim\Enrichment\Component\Comment\Repository\CommentRepositoryInterface;
use Akeneo\Platform\Bundle\UIBundle\Form\Type\ObjectIdentifierType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

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

    /** @var TranslatorInterface  */
    protected $translator;

    /** @var string */
    protected $dataClass;

    /**
     * @param CommentRepositoryInterface $repository
     * @param TranslatorInterface        $translator
     * @param string                     $dataClass
     */
    public function __construct(CommentRepositoryInterface $repository, TranslatorInterface $translator, $dataClass)
    {
        $this->repository = $repository;
        $this->translator = $translator;
        $this->dataClass = $dataClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $placeholder = (true === $options['is_reply']) ? 'pim_enrich.entity.product.module.comment.reply' : 'pim_enrich.entity.product.module.comment.post';
        $placeholder = $this->translator->trans($placeholder);

        $builder
            ->add(
                'body',
                TextareaType::class,
                ['label' => false, 'attr' => ['placeholder' => $placeholder, 'class' => 'exclude']]
            )
            ->add('resourceName', HiddenType::class)
            ->add('resourceId', HiddenType::class);

        if (true === $options['is_reply']) {
            $builder->add(
                'parent',
                ObjectIdentifierType::class,
                [
                    'multiple' => false,
                    'repository' => $this->repository
                ]
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
                'data_class' => $this->dataClass,
                'is_reply'   => false
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'pim_comment_comment';
    }
}
