<?php

namespace Pim\Bundle\EnrichBundle\Form\Handler;

use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Manager\GroupManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Component\Resource\Model\SaverInterface;
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

    /**
     * Constructor for handler
     *
     * @param FormInterface              $form
     * @param Request                    $request
     * @param SaverInterface             $groupSaver
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        SaverInterface $groupSaver,
        ProductRepositoryInterface $productRepository
    ) {
        $this->form       = $form;
        $this->request    = $request;
        $this->groupSaver = $groupSaver;
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function process($group)
    {
        $this->form->setData($group);

        if ($this->request->isMethod('POST')) {
            // Load products when ODM storage is used to enable validation
            // TODO : how to fix this for validation ?
            if (null === $group->getProducts()) {
                $products = $this->productRepository->findAllForGroup($group)->toArray();
                $group->setProducts($products);
            }

            $this->form->submit($this->request);

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
        $appendProducts = $this->form->get('appendProducts')->getData();
        $removeProducts = $this->form->get('removeProducts')->getData();
        $options = ['append_products' => $appendProducts, 'remove_products' => $removeProducts];
        $this->groupSaver->save($group, $options);
    }
}
