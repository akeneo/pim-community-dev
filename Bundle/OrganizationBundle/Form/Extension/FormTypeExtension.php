<?php

namespace Oro\Bundle\OrganizationBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\OrganizationBundle\Form\Type\OwnershipType;

class FormTypeExtension extends AbstractTypeExtension
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

    /**
    * Returns the name of the type being extended.
    *
    * @return string The name of the type being extended
    */
    public function getExtendedType()
    {
        return 'form';
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $dataClassName = $builder->getForm()->getConfig()->getDataClass();
        $token = $this->securityContext->getToken();
        if ($token) {
            $user = $token->getUser();
            if ($user && $dataClassName) {
                if ($this->configProvider->hasConfig($dataClassName)) {
                    /** @var $config EntityConfig */
                    $config = $this->configProvider->getConfig($dataClassName);
                    $entityValues = $config->getValues();
                    if (isset($entityValues['owner_type'])) {
                        if (OwnershipType::OWNERSHIP_TYPE_USER == $entityValues['owner_type']) {
                            $builder->add('owner', 'oro_user_select', array('required' => true));
                        } elseif (OwnershipType::OWNERSHIP_TYPE_BUSINESS_UNIT == $entityValues['owner_type']) {
                            $builder->add('owner', 'oro_business_unit_select', array('required' => true));

                            /*$builder->add(
                                'owner',
                                'entity',
                                array(
                                    'class' => 'OroOrganizationBundle:BusinessUnit',
                                    'property' => 'name',
                                    'choices' => $user->getBusinessUnits(),
                                    'mapped' => false
                                )
                            );*/
                        } elseif (OwnershipType::OWNERSHIP_TYPE_ORGANIZATION == $entityValues['owner_type']) {
                            $organizations = array();
                            $bu = $user->getBusinessUnits();
                            foreach ($bu as $businessUnit) {
                                $organizations[] = $businessUnit->getOrganization();
                            }
                            $builder->add(
                                'owner',
                                'entity',
                                array(
                                    'class' => 'OroOrganizationBundle:Organization',
                                    'property' => 'name',
                                    'choices' => $organizations,
                                    'mapped' => false
                                )
                            );
                        }
                    }
                }
            }
        }
    }
}