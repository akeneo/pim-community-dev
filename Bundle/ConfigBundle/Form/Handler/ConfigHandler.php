<?php

namespace Oro\Bundle\ConfigBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class ConfigHandler
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @param Request $request
     * @param ObjectManager $manager
     */
    public function __construct(Request $request, ObjectManager $manager)
    {
        $this->request = $request;
        $this->manager = $manager;
    }

    /**
     * Process form
     *
     * @param FormInterface $form
     * @return bool True on successful processing, false otherwise
     */
    public function process(FormInterface $form)
    {
        $form->setData($entity);

        if (in_array($this->request->getMethod(), array('POST', 'PUT'))) {
            $form->submit($this->request);

            if ($form->isValid()) {
                $this->manager->persist($entity);
                $this->manager->flush();

                return true;
            }
        }

        return false;
    }
}
