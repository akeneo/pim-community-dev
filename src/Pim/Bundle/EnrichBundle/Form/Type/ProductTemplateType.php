<?php

namespace Pim\Bundle\EnrichBundle\Form\Type;

use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\EnrichBundle\Form\Subscriber\TransformProductTemplateValuesSubscriber;
use Pim\Bundle\EnrichBundle\Form\View\ProductFormViewInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Product template form type
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductTemplateType extends AbstractType
{
    /** @var string */
    protected $productTemplateClass;

    /** @var ProductFormViewInterface */
    protected $productFormView;

    /** @var TransformProductTemplateValuesSubscriber */
    protected $valuesSubscriber;

    /** @var UserContext */
    protected $userContext;

    /** @var ChannelManager */
    protected $channelManager;

    /**
     * @param string                                   $productTemplateClass
     * @param ProductFormViewInterface                 $productFormView
     * @param TransformProductTemplateValuesSubscriber $valuesSubscriber
     * @param UserContext                              $userContext
     * @param ChannelManager                           $channelManager
     */
    public function __construct(
        // TODO (JJ) should be the last param
        $productTemplateClass,
        ProductFormViewInterface $productFormView,
        TransformProductTemplateValuesSubscriber $valuesSubscriber,
        UserContext $userContext,
        ChannelManager $channelManager
    ) {
        $this->productTemplateClass = $productTemplateClass;
        $this->productFormView      = $productFormView;
        $this->valuesSubscriber     = $valuesSubscriber;
        $this->userContext          = $userContext;
        $this->channelManager       = $channelManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'values',
                'pim_enrich_localized_collection',
                array(
                    'type'               => 'pim_product_value',
                    'allow_add'          => false,
                    'allow_delete'       => false,
                    'by_reference'       => false,
                    'cascade_validation' => true,
                    'currentLocale'      => $options['currentLocale']
                )
            )
            ->addEventSubscriber($this->valuesSubscriber);
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $values = null !== $view->vars['value'] ? $view->vars['value']->getValues() : [];

        $view->vars['groups']        = $this->productFormView->getView();
        $view->vars['orderedGroups'] = $this->getOrderedGroups($values);
        $view->vars['locales']       = $this->userContext->getUserLocales();
        $view->vars['channels']      = $this->channelManager->getChannels();
        $view->vars['currentLocale'] = $options['currentLocale'];
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'    => $this->productTemplateClass,
                'currentLocale' => null,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_enrich_product_template';
    }

    /**
     * Returns attribute groups of the given values ordered by their sort order
     *
     * TODO (JJ) FQCN or use statement
     * @param ProductValueInterface[]|ArrayCollection $values
     *
     * @return AttributeGroup[]
     */
    protected function getOrderedGroups($values)
    {
        $attributes = [];

        foreach ($values as $value) {
            $attributes[] = $value->getAttribute();
        }

        $groups = [];
        foreach ($attributes as $attribute) {
            $group = $attribute->getGroup();
            $groups[$group->getId()] = $group;
        }

        $sortGroup = function (AttributeGroup $fst, AttributeGroup $snd) {
            return $fst->getSortOrder() - $snd->getSortOrder();
        };

        usort($groups, $sortGroup);

        return $groups;
    }
}
