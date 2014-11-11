<?php

namespace Pim\Bundle\EnrichBundle\Form\Handler;

use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Manager\AssociationTypeManager;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Form handler for association type
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationTypeHandler
{
    /** @var FormInterface */
    protected $form;

    /** @var Request */
    protected $request;

    /** @var AssociationTypeManager */
    protected $manager;

    /**
     * Constructor for handler
     *
     * @param FormInterface          $form    Form called
     * @param Request                $request Web request
     * @param AssociationTypeManager $manager Association type manager
     */
    public function __construct(FormInterface $form, Request $request, AssociationTypeManager $manager)
    {
        $this->form    = $form;
        $this->request = $request;
        $this->manager = $manager;
    }

    /**
     * Process method for handler
     * @param AssociationType $associationType
     *
     * @return boolean
     */
    public function process(AssociationType $associationType)
    {
        $this->form->setData($associationType);

        if ($this->request->isMethod('POST')) {
            $this->form->submit($this->request);

            if ($this->form->isValid()) {
                $this->onSuccess($associationType);

                return true;
            }
        }

        return false;
    }

    /**
     * Call when form is valid
     *
     * @param AssociationType $associationType
     */
    protected function onSuccess(AssociationType $associationType)
    {
        $this->manager->save($associationType);
    }
}
