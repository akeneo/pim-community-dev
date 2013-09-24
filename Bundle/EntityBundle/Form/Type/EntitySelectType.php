<?php

namespace Oro\Bundle\EntityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\EntityBundle\ORM\OroEntityManager;

class EntitySelectType extends AbstractType
{
    const NAME = 'oro_entity_select';

    /**
     * @var OroEntityManager
     */
    protected $entityManager;

    /**
     * @param OroEntityManager $entityManager
     */
    public function __construct(OroEntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $vars = array('configs' => $options['configs']);
        if ($form->getData()) {
            $fieldConfig = $this->entityManager->getExtendManager()->getConfigProvider()->getConfig(
                $form->getParent()->getConfig()->getDataClass(),
                $form->getName()
            );

            $fieldName = $fieldConfig->get('target_field');

            $vars['attr'] = array(
                'data-entities' => json_encode(
                    array(
                        array(
                            'text' => $form->getData()->{'get' . ucfirst($fieldName)}(),
                        )
                    )
                )
            );
        }

        $view->vars = array_replace_recursive($view->vars, $vars);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'placeholder'        => 'oro.form.choose_value',
                'allowClear'         => true,
                'autocomplete_alias' => 'entity_select',
                'configs'            => array(
                    'placeholder' => 'oro.form.choose_value',
                    //'extra_config'       => 'autocomplete',
                    //'route_name'         => 'oro_entity_search',
                    'properties'  => array('id', 'text')
                )
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'oro_jqueryselect2_hidden';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
