<?php

namespace Pim\Bundle\EnrichBundle\Form\Handler;

use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Manager\AttributeGroupManager;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Form handler for attribute groups
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupHandler
{
    /** @var FormInterface */
    protected $form;

    /** @var Request */
    protected $request;

    /** @var AttributeGroupManager */
    protected $manager;

    /**
     * Constructor for handler
     *
     * @param FormInterface         $form    Form called
     * @param Request               $request Web request
     * @param AttributeGroupManager $manager Attribute group manager
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        AttributeGroupManager $manager
    ) {
        $this->form    = $form;
        $this->request = $request;
        $this->manager = $manager;
    }

    /**
     * Process method for handler
     * @param AttributeGroup $group
     *
     * @return boolean
     */
    public function process(AttributeGroup $group)
    {
        $this->form->setData($group);

        if ($this->request->isMethod('POST')) {
            $this->form->bind($this->request);

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
     * @param AttributeGroup $group
     */
    protected function onSuccess(AttributeGroup $group)
    {
        $this->manager->save($group);
    }
}
