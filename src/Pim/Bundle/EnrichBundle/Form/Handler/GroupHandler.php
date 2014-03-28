<?php

namespace Pim\Bundle\EnrichBundle\Form\Handler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Entity\Group;

/**
 * Form handler for group
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupHandler
{
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var string
     */
    protected $productClass;

    /**
     * Constructor for handler
     * @param FormInterface   $form
     * @param Request         $request
     * @param ManagerRegistry $registry
     * @param string          $productClass
     */
    public function __construct(FormInterface $form, Request $request, ManagerRegistry $registry, $productClass)
    {
        $this->form         = $form;
        $this->request      = $request;
        $this->registry     = $registry;
        $this->productClass = $productClass;
    }

    /**
     * Process method for handler
     * @param Group $group
     *
     * @return boolean
     */
    public function process(Group $group)
    {
        $this->form->setData($group);

        if ($this->request->isMethod('POST')) {
            // Load products when ODM storage is used to enable validation
            if (null === $group->getProducts()) {
                $products = $this->registry->getRepository($this->productClass)->findAllForGroup($group)->toArray();
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
     * @param Group $group
     */
    protected function onSuccess(Group $group)
    {
        $groupManager = $this->registry->getManagerForClass(get_class($group));
        $groupManager->persist($group);

        $productManager = $this->registry->getManagerForClass($this->productClass);
        $appendProducts = $this->form->get('appendProducts')->getData();
        $removeProducts = $this->form->get('removeProducts')->getData();
        $products = $appendProducts + $removeProducts;

        foreach ($products as $product) {
            $productManager->persist($product);
        }

        $productManager->flush();
        $groupManager->flush();
    }
}
