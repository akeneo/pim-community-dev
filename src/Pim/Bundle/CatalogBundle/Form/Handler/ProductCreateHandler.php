<?php

namespace Pim\Bundle\CatalogBundle\Form\Handler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Calculator\CompletenessCalculator;

/**
 * Form handler for product creation form type
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCreateHandler
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
     * @var ProductManager
     */
    protected $manager;

    /**
     * @var CompletenessCalculator
     */
    protected $calculator;

    /**
     * Constructor for handler
     * @param FormInterface          $form       Form called
     * @param Request                $request    Web request
     * @param ProductManager         $manager    Product manager
     * @param CompletenessCalculator $calculator Completeness calculator
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        ProductManager $manager,
        CompletenessCalculator $calculator
    ) {
        $this->form       = $form;
        $this->request    = $request;
        $this->manager    = $manager;
        $this->calculator = $calculator;
    }

    /**
     * Process method for handler
     * @param ProductInterface $entity
     *
     * @return boolean
     */
    public function process(ProductInterface $entity)
    {
        $this->form->setData($entity);

        if ($this->request->isMethod('POST')) {
            $this->form->bind($this->request);

            if ($this->form->isValid()) {
                $this->onSuccess($entity);

                return true;
            }
        }

        return false;
    }

    /**
     * Call when form is valid
     * @param ProductInterface $entity
     */
    protected function onSuccess(ProductInterface $entity)
    {
        $this->manager->save($entity);

        $product = $this->manager->find($entity->getId());
        $this->calculator->calculateForAProduct($product);

        $this->manager->save($product);
    }
}
