<?php

namespace Oro\Bundle\ConfigBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class ConfigHandler
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ConfigManager
     */
    protected $manager;

    /**
     * @param ConfigManager $manager
     */
    public function __construct(ConfigManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Process form
     *
     * @param FormInterface $form
     *
     * @param Request $request
     * @return bool True on successful processing, false otherwise
     */
    public function process(FormInterface $form, Request $request)
    {
        $settingsData = $this->manager->getSettingsByForm($form);
        $form->setData($settingsData);

        if (in_array($this->request->getMethod(), array('POST', 'PUT'))) {
            $form->submit($request);
            if ($form->isValid()) {
                $this->manager->save($form->getData());

                return true;
            }
        }

        return false;
    }
}
