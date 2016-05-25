<?php

namespace Pim\Bundle\EnrichBundle\Form\Type;

use Pim\Component\Catalog\Model\FamilyInterface;
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
class ChangeFamilyType extends AbstractType
{
    /** @var FamilyRepositoryInterface */
    protected $familyRepository;

    /**
     * @param RouterInterface           $router
     * @param FamilyRepositoryInterface $familyRepository
     */
    public function __construct(RouterInterface $router, FamilyRepositoryInterface $familyRepository)
    {
        $this->router = $router;
        $this->familyRepository = $familyRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'change_family_type';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'pim_async_select';
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
        $view->vars['attr']['data-choices'] = json_encode($this->normalizeFamily($form->getData()));
    }

    /**
     * Normalizes families for the select2
     *
     * @param $familyCodes
     *
     * @return array
     */
    protected function normalizeFamily($familyCodes)
    {
        $familyCodes = explode(',', $familyCodes);

        $result = [];
        foreach ($familyCodes as $familyCode) {
            $family = $this->familyRepository->findOneByIdentifier($familyCode);
            if ($family instanceof FamilyInterface) {
                $familyLabel = $family->getLabel();
                $result[] = ['id' => $familyCode, 'text' => $familyLabel];
            }
        }

        return $result;
    }
}
