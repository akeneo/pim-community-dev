<?php

namespace Pim\Bundle\EnrichBundle\Form\Handler;

use Pim\Bundle\CatalogBundle\Manager\FamilyManager;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use PIm\Bundle\CatalogBundle\Manager\CompletenessManager;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

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
     * @var FamilyManager
     */
    protected $manager;

    /**
     * @var CompletenessManager
     */
    protected $completenessManager;

    /**
     * Constructor for handler
     *
     * @param FormInterface       $form
     * @param Request             $request
     * @param FamilyManager       $manager
     * @param CompletenessManager $completenessManager
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        FamilyManager $manager,
        CompletenessManager $completenessManager
    ) {
        $this->form    = $form;
        $this->request = $request;
        $this->manager = $manager;

        $this->completenessManager = $completenessManager;
    }

    /**
     * Process method for handler
     *
     * @param FamilyInterface $family
     *
     * @return boolean
     */
    public function process(FamilyInterface $family)
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
     * @param FamilyInterface $family
     */
    protected function onSuccess(FamilyInterface $family)
    {
        $this->manager->save($family);
        $this->completenessManager->scheduleForFamily($family);
    }
}
