<?php

namespace Pim\Bundle\CatalogBundle\Form\Handler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Entity\Association;

/**
 * Form handler for association
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationHandler
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
     * @param Association $association
     *
     * @return boolean
     */
    public function process(Association $association)
    {
        $this->form->setData($association);

        if ($this->request->isMethod('POST')) {
            $this->form->submit($this->request);

            if ($this->form->isValid()) {
                $this->onSuccess($association);

                return true;
            }
        }

        return false;
    }

    /**
     * Call when form is valid
     * @param Association $association
     */
    protected function onSuccess(Association $association)
    {
        $this->manager->persist($association);
        $this->manager->flush();
    }
}
