<?php

namespace Pim\Bundle\CatalogBundle\Form\Handler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\VersioningBundle\Manager\PendingManager;

/**
 * Form handler for attribute groups
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupHandler
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
     * @var PendingManager
     */
    protected $pendingManager;

    /**
     * Constructor for handler
     * @param FormInterface  $form           Form called
     * @param Request        $request        Web request
     * @param ObjectManager  $manager        Storage manager
     * @param PendingManager $pendingManager Pending manager
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        ObjectManager $manager,
        PendingManager $pendingManager
    ) {
        $this->form           = $form;
        $this->request        = $request;
        $this->manager        = $manager;
        $this->pendingManager = $pendingManager;
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
     * @param AttributeGroup $group
     */
    protected function onSuccess(AttributeGroup $group)
    {
        $this->manager->persist($group);
        $this->manager->flush();

        if ($pending = $this->pendingManager->getPendingVersion($group)) {
            $this->pendingManager->createVersionAndAudit($pending);
        }
    }
}
