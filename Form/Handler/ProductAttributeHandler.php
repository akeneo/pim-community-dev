<?php
namespace Pim\Bundle\ProductBundle\Form\Handler;

use Pim\Bundle\ProductBundle\Entity\ProductAttribute;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Form\FormInterface;

/**
 * Form handler for Product attribute
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class ProductAttributeHandler
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
     *
     * @param FormInterface $form
     * @param Request $request
     * @param ObjectManager $manager
     */
    public function __construct(FormInterface $form, Request $request, ObjectManager $manager)
    {
        $this->form    = $form;
        $this->request = $request;
        $this->manager = $manager;
    }

    public function process(ProductAttribute $entity)
    {
        $this->form->setData($entity);

        if (in_array($this->request->getMethod(), array('POST'))) {
            $this->form->bind($this->request);

            if ($this->form->isValid()) {
                $this->onSuccess($entity);

                return true;
            }
        }

        return false;
    }

    protected function onSuccess(ProductAttribute $entity)
    {
        $this->manager->persist($entity);
        $this->manager->flush();
    }

}
