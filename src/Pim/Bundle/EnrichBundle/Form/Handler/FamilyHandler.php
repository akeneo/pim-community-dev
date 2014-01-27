<?php

namespace Pim\Bundle\EnrichBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Entity\Family;

/**
 * Form handler for family
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyHandler
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
     *
     * @param FormInterface $form
     * @param Request       $request
     * @param ObjectManager $objectManager
     */
    public function __construct(FormInterface $form, Request $request, ObjectManager $objectManager)
    {
        $this->form    = $form;
        $this->request = $request;
        $this->manager = $objectManager;
    }

    /**
     * Process method for handler
     *
     * @param Family $family
     *
     * @return boolean
     */
    public function process(Family $family)
    {
        $this->form->setData($family);

        if ($this->request->isMethod('POST')) {
            $this->form->submit($this->request);

            if ($this->form->isValid()) {
                $this->onSuccess($family);

                return true;
            }
        }

        return false;
    }

    /**
     * Call when form is valid
     *
     * @param Family $family
     */
    protected function onSuccess(Family $family)
    {
        $this->manager->persist($family);
        $this->manager->flush();
    }
}
