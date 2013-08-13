<?php
namespace Oro\Bundle\OrganizationBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\OrganizationBundle\Form\Type\OwnershipType;

class AddOwnershipFieldListener implements EventSubscriberInterface
{
    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    protected $configProvider;

    public function __construct($securityContext, ConfigProvider $configProvider)
    {
        $this->securityContext = $securityContext;
        $this->configProvider = $configProvider;
    }

    public static function getSubscribedEvents()
    {
        return array(FormEvents::PRE_SET_DATA => 'preSetData');
    }

    public function preSetData(FormEvent $event)
    {
        //$data = $event->getData();
        $form = $event->getForm();
        $dataClassName = $form->getConfig()->getDataClass();

        $token = $this->securityContext->getToken();
        if ($token) {
            $user = $token->getUser();
            if ($user) {
                if ($this->configProvider->hasConfig($dataClassName)) {
                    /** @var $config EntityConfig */
                    $config = $this->configProvider->getConfig($dataClassName);
                    $entityValues = $config->getValues();

                    if (OwnershipType::OWNERSHIP_TYPE_BUSINESS_UNIT == $entityValues['owner_type']) {
                        $form->add('owner', 'entity', array(
                            'class' => 'OroOrganizationBundle:BusinessUnit',
                            'property' => 'name',
                            'choices' => $user->getBusinessUnits(),
                            'mapped' => false
                        ));
                    } elseif (OwnershipType::OWNERSHIP_TYPE_ORGANIZATION == $entityValues['owner_type']) {
                        $organizations = array();
                        $bu = $user->getBusinessUnits();
                        foreach ($bu as $businessUnit) {
                            $organizations[] = $businessUnit->getOrganization();
                        }
                        $form->add('owner', 'entity', array(
                            'class' => 'OroOrganizationBundle:Organization',
                            'property' => 'name',
                            'choices' => $organizations,
                            'mapped' => false
                        ));
                    }
                }
            }
        }
    }
}
