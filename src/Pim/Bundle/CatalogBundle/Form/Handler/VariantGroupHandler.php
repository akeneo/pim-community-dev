<?php

namespace Pim\Bundle\CatalogBundle\Form\Handler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Entity\VariantGroup;

/**
 * Form handler for variant group
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupHandler
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
     * @var ObjectManager
     */
    protected $manager;

    /**
     * Constructor for handler
     * @param FormInterface $form    Form called
     * @param Request       $request Web request
     * @param ObjectManager $manager Storage manager
     */
    public function __construct(FormInterface $form, Request $request, ObjectManager $manager)
    {
        $this->form    = $form;
        $this->request = $request;
        $this->manager = $manager;
    }

    /**
     * Process method for handler
     * @param VariantGroup $variantGroup
     *
     * @return boolean
     */
    public function process(VariantGroup $variantGroup)
    {
        $this->form->setData($variantGroup);

        if ($this->request->isMethod('POST')) {
            $this->form->submit($this->request);

            $this->bindProducts($variantGroup);

            if ($this->form->isValid()) {
                $this->onSuccess($variantGroup);

                return true;
            }
        }

        return false;
    }

    /**
     * Bind products
     *
     * @param VariantGroup $variantGroup
     */
    private function bindProducts(VariantGroup $variantGroup)
    {
        $appendProducts = $this->form->get('appendProducts')->getData();
        $removeProducts = $this->form->get('removeProducts')->getData();

        foreach ($appendProducts as $product) {
            $variantGroup->addProduct($product);
            $product->setVariantGroup($variantGroup);
        }

        foreach ($removeProducts as $product) {
            $variantGroup->removeProduct($product);
            $product->setVariantGroup(null);
        }
    }

    /**
     * Call when form is valid
     * @param VariantGroup $variantGroup
     */
    protected function onSuccess(VariantGroup $variantGroup)
    {
        $this->manager->persist($variantGroup);
        $this->manager->flush();
    }
}
