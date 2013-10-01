<?php

namespace Oro\Bundle\OrganizationBundle\Event;

use Oro\Bundle\UIBundle\Event\BeforeFormRenderEvent;

class FormListener
{
    /**
     * Add owner field to forms
     *
     * @param BeforeFormRenderEvent $event
     */
    public function addOwnerField(BeforeFormRenderEvent $event)
    {
        $environment = $event->getTwigEnvironment();
        $data = $event->getFormData();
        $form = $event->getForm();

        $ownerField = $environment->render(
            "OroOrganizationBundle::owner.html.twig",
            array(
                'form' => $form
            )
        );
        /**
         * Setting owner field as last field in first data block
         */
        if (!empty($data['dataBlocks'])) {
            if (isset($data['dataBlocks'][0]['subblocks'])) {
                $data['dataBlocks'][0]['subblocks'][0]['data'][] = $ownerField;
            }
        }

        $event->setFormData($data);
    }
}
