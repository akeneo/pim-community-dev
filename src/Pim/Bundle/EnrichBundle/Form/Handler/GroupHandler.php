<?php

namespace Pim\Bundle\EnrichBundle\Form\Handler;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Localization\Localizer\LocalizedAttributeConverterInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Form handler for group
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupHandler implements HandlerInterface
{
    /** @var FormInterface */
    protected $form;

    /** @var Request */
    protected $request;

    /** @var SaverInterface */
    protected $groupSaver;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var LocalizedAttributeConverterInterface */
    protected $localizedConverter;

    /**
     * Constructor for handler
     *
     * @param FormInterface                        $form
     * @param Request                              $request
     * @param SaverInterface                       $groupSaver
     * @param ProductRepositoryInterface           $productRepository
     * @param LocalizedAttributeConverterInterface $localizedConverter
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        SaverInterface $groupSaver,
        ProductRepositoryInterface $productRepository,
        LocalizedAttributeConverterInterface $localizedConverter
    ) {
        $this->form               = $form;
        $this->request            = $request;
        $this->groupSaver         = $groupSaver;
        $this->productRepository  = $productRepository;
        $this->localizedConverter = $localizedConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function process($group)
    {
        $this->form->setData($group);

        if ($this->request->isMethod('POST')) {
            // TODO : how to fix this ? Load products when ODM storage is used to enable validation
            if (null === $group->getProducts()) {
                $products = $this->productRepository->findAllForGroup($group)->toArray();
                $group->setProducts($products);
            }

            $this->form->submit($this->request);

            if ($this->form->isValid()) {
                $this->onSuccess($group);

                return true;
            } elseif ($group->getType()->isVariant() && $group->getId()) {
                $products = $this->productRepository->findAllForVariantGroup($group);
                $group->setProducts($products);
            }
        }

        return false;
    }

    /**
     * Call when form is valid
     *
     * @param GroupInterface $group
     */
    protected function onSuccess(GroupInterface $group)
    {
        $appendProducts = $this->form->get('appendProducts')->getData();
        $removeProducts = $this->form->get('removeProducts')->getData();
        $options = [
            'add_products'    => $appendProducts,
            'remove_products' => $removeProducts
        ];
        if ($group->getType()->isVariant()) {
            $options['copy_values_to_products'] = true;
            $this->convertLocalizedValues($group);
        }
        $this->groupSaver->save($group, $options);
    }

    /**
     * Convert localized values in template of a variant group
     *
     * @param GroupInterface $group
     */
    protected function convertLocalizedValues(GroupInterface $group)
    {
        $template = $group->getProductTemplate();

        if (null === $template) {
            return;
        }

        $options    = ['locale' => $this->request->getLocale(), 'disable_grouping_separator' => true];
        $valuesData = $this->localizedConverter->convertLocalizedToDefaultValues($template->getValuesData(), $options);

        $template->setValuesData($valuesData);
    }
}
