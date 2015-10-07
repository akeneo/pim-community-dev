<?php

namespace Oro\Bundle\ConfigBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class ConfigHandler
{
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

        if (in_array($request->getMethod(), array('POST', 'PUT'))) {
            $form->submit($request);
            if ($form->isValid()) {
                $this->manager->save($form->getData());

                return true;
            }
        }

        return false;
    }
}
