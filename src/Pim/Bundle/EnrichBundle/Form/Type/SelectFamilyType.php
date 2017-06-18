<?php

namespace Pim\Bundle\EnrichBundle\Form\Type;

use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\RouterInterface;

/**
 * Select form type
 * A form type to display an asynchronous dropdown
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SelectFamilyType extends AbstractType
{
    /** @var RouterInterface */
    protected $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'select_family_type';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return AsyncSelectType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->resetViewTransformers();
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if ($options['multiple']) {
            $view->vars['attr']['data-multiple'] = 'multiple';
        }
        $view->vars['attr']['data-choices'] = json_encode(
            $this->normalizeFamily($options['repository'], $form->getData())
        );
    }

    /**
     * Normalizes families for the select2
     *
     * @param FamilyRepositoryInterface $familyRepository
     * @param string                    $familyCodes
     * @return array
     */
    protected function normalizeFamily(FamilyRepositoryInterface $familyRepository, $familyCodes)
    {
        $familyCodes = explode(',', $familyCodes);

        $result = [];
        $families = $familyRepository->findBy(['code' => $familyCodes]);
        foreach ($families as $family) {
            $familyLabel = $family->getLabel();
            $result[] = ['id' => $family->getCode(), 'text' => $familyLabel];
        }

        return $result;
    }
}
