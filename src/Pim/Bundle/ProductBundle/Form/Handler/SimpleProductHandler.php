<?php
namespace Pim\Bundle\ProductBundle\Form\Handler;

use Pim\Bundle\ProductBundle\Entity\Product;
use Pim\Bundle\ProductBundle\Manager\ProductManager;
use Pim\Bundle\ProductBundle\Entity\ProductValue;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;

/**
 * Form handler for simple product type
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class SimpleProductHandler
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
     * Constructor for handler
     * @param FormInterface  $form    Form called
     * @param Request        $request Web request
     * @param ProductManager $manager Product manager
     */
    public function __construct(FormInterface $form, Request $request, ProductManager $manager)
    {
        $this->form    = $form;
        $this->request = $request;
        $this->manager = $manager;
    }

    /**
     * Process method for handler
     * @param Product $entity
     *
     * @return boolean
     */
    public function process(Product $entity)
    {
        $this->form->setData($entity);

        if ($this->request->getMethod() === 'POST') {
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
     * @param Product $entity
     */
    protected function onSuccess(Product $entity)
    {
        $this->manager->save($entity);
    }
}
