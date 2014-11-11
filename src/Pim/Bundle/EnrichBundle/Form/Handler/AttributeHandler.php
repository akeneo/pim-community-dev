<?php

namespace Pim\Bundle\EnrichBundle\Form\Handler;

use Pim\Bundle\CatalogBundle\Manager\AttributeManager;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Form handler for attribute
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeHandler
{
    /** @var FormInterface */
    protected $form;

    /**  @var Request */
    protected $request;

    /** @var AttributeManager */
    protected $manager;

    /**
     * Constructor for handler
     * @param FormInterface    $form    Form called
     * @param Request          $request Web request
     * @param AttributeManager $manager Attribute manager
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        AttributeManager $manager
    ) {
        $this->form    = $form;
        $this->request = $request;
        $this->manager = $manager;
    }

    /**
     * Process method for handler
     * @param AbstractAttribute $entity
     *
     * @return boolean
     */
    public function process(AbstractAttribute $entity)
    {
        $this->form->setData($entity);

        if ($this->request->isMethod('POST')) {
            $this->form->submit($this->request);

            if ($this->form->isValid()) {
                $this->manager->save($entity);

                return true;
            }
        }

        return false;
    }
}
