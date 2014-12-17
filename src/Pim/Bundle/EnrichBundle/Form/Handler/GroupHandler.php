<?php

namespace Pim\Bundle\EnrichBundle\Form\Handler;

use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Manager\GroupManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
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

    /** @var GroupManager */
    protected $groupManager;

    /** @var ProductManager */
    protected $productManager;

    /**
     * Constructor for handler
     *
     * @param FormInterface  $form
     * @param Request        $request
     * @param GroupManager   $groupManager
     * @param ProductManager $productManager
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        GroupManager $groupManager,
        ProductManager $productManager
    ) {
        $this->form           = $form;
        $this->request        = $request;
        $this->groupManager   = $groupManager;
        $this->productManager = $productManager;
    }

    /**
     * {@inheritdoc}
     */
    public function process($group)
    {
        $this->form->setData($group);

        if ($this->request->isMethod('POST')) {
            // Load products when ODM storage is used to enable validation
            if (null === $group->getProducts()) {
                $products = $this->productManager->getProductRepository()->findAllForGroup($group)->toArray();
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
     * @param Group $group
     */
    protected function onSuccess(Group $group)
    {
        $this->groupManager->save($group);

        $appendProducts = $this->form->get('appendProducts')->getData();
        $removeProducts = $this->form->get('removeProducts')->getData();
        $products = $appendProducts + $removeProducts;

        $this->productManager->saveAll($products, ['recalculate' => false, 'schedule' => false]);
    }
}
