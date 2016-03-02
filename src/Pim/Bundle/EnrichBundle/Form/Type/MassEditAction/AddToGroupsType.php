<?php

namespace Pim\Bundle\EnrichBundle\Form\Type\MassEditAction;

use Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Add to groups mass action form type
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddToGroupsType extends AbstractType
{
    /** @var string */
    protected $dataClass;

    /** @var string */
    protected $groupClassName;

    /** @var GroupRepositoryInterface */
    protected $groupRepository;

    /** @var array */
    protected $warningMessages;

    /**
     * @param GroupRepositoryInterface $groupRepository
     * @param string                   $groupClassName
     * @param string                   $dataClass
     */
    public function __construct(GroupRepositoryInterface $groupRepository, $groupClassName, $dataClass)
    {
        $this->groupClassName  = $groupClassName;
        $this->dataClass       = $dataClass;
        $this->groupRepository = $groupRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'groups',
            'entity',
            [
                'class'    => $this->groupClassName,
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'choices'  => $options['groups'],
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['warningMessages'] = $this->getWarningMessages();
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => $this->dataClass,
                'groups'     => $this->groupRepository->getAllGroupsExceptVariant()
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_enrich_mass_add_to_groups';
    }

    /**
     * Get warning messages
     *
     * @return array
     */
    protected function getWarningMessages()
    {
        if (null === $this->warningMessages) {
            $this->warningMessages = $this->generateWarningMessages();
        }

        return $this->warningMessages;
    }

    /**
     * Get warning messages to display during the mass edit action
     *
     * @return string[]
     */
    protected function generateWarningMessages()
    {
        $messages = [];

        if (0 === count($this->groupRepository->getAllGroupsExceptVariant())) {
            $messages[] = [
                'key'     => 'pim_enrich.mass_edit_action.add-to-groups.no_group',
                'options' => [],
            ];
        }

        return $messages;
    }
}
