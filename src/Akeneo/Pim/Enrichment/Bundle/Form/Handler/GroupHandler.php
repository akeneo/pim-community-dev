<?php

namespace Akeneo\Pim\Enrichment\Bundle\Form\Handler;

use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Form handler for group
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupHandler
{
    /** @var FormInterface */
    protected $form;

    /** @var RequestStack */
    protected $requestStack;

    /** @var SaverInterface */
    protected $groupSaver;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var AttributeConverterInterface */
    protected $localizedConverter;

    /**
     * Constructor for handler
     *
     * @param FormInterface               $form
     * @param RequestStack                $requestStack
     * @param SaverInterface              $groupSaver
     * @param ProductRepositoryInterface  $productRepository
     * @param AttributeConverterInterface $localizedConverter
     */
    public function __construct(
        FormInterface $form,
        RequestStack $requestStack,
        SaverInterface $groupSaver,
        ProductRepositoryInterface $productRepository,
        AttributeConverterInterface $localizedConverter
    ) {
        $this->form               = $form;
        $this->requestStack       = $requestStack;
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

        if ($this->requestStack->getCurrentRequest()->isMethod('POST')) {
            $this->form->handleRequest($this->requestStack->getCurrentRequest());
            if ($this->form->isValid()) {
                $this->onSuccess($group);

                return true;
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
        $options = ['copy_values_to_products' => true];
        $this->groupSaver->save($group, $options);
    }
}
