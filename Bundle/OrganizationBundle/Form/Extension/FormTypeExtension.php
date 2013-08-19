<?php

namespace Oro\Bundle\OrganizationBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use Oro\Bundle\UserBundle\EventListener\RecordOwnerDataListener;
use Oro\Bundle\OrganizationBundle\Entity\Manager\BusinessUnitManager;
use Oro\Bundle\UserBundle\Acl\Manager as AclManager;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\OrganizationBundle\Form\Type\OwnershipType;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;

class FormTypeExtension extends AbstractTypeExtension
{
    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @var BusinessUnitManager
     */
    protected $manager;

    /**
     * @var AclManager
     */
    protected $aclManager;

    public function __construct(
        SecurityContextInterface $securityContext,
        ConfigProvider $configProvider,
        BusinessUnitManager $manager,
        AclManager $aclManager
    ) {
        $this->securityContext = $securityContext;
        $this->configProvider = $configProvider;
        $this->manager = $manager;
        $this->aclManager = $aclManager;
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

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fieldName = RecordOwnerDataListener::OWNER_FIELD_NAME;
        $changeOwnerGranted = $this->aclManager->isResourceGranted('oro_change_record_owner');
        $dataClassName = $builder->getForm()->getConfig()->getDataClass();
        $token = $this->securityContext->getToken();
        if ($token) {
            $user = $token->getUser();
            if ($user && $dataClassName) {
                if ($this->configProvider->hasConfig($dataClassName)) {
                    $config = $this->configProvider->getConfig($dataClassName);
                    $entityValues = $config->getValues();
                    if (isset($entityValues['owner_type'])) {
                        $ownerType = $entityValues['owner_type'];
                        /**
                         * Adding listener to hide owner field for update pages
                         * if change owner permission is not granted
                         */
                        $builder->addEventListener(
                            FormEvents::POST_SET_DATA,
                            function (FormEvent $event) use ($fieldName, $changeOwnerGranted) {
                                $form = $event->getForm();
                                if ($form->getParent()) {
                                    return;
                                }
                                $entity = $event->getData();

                                if (is_object($entity)
                                    && $entity->getId()
                                    && $form->has($fieldName)
                                    && !$changeOwnerGranted
                                ) {
                                    $owner = $form->get($fieldName)->getData();
                                    $form->remove($fieldName);
                                    $form->add(
                                        $fieldName,
                                        'text',
                                        array(
                                            'disabled' => true,
                                            'data' => $owner ? $owner->getName() : '',
                                            'mapped' => false,
                                            'required' => false
                                        )
                                    );
                                }
                            }
                        );
                        if (OwnershipType::OWNERSHIP_TYPE_USER == $ownerType && $changeOwnerGranted) {
                            /**
                             * Showing user owner box for entities with owner type USER if change owner permission is
                             * granted.
                             */
                            $builder->add($fieldName, 'oro_user_select', array('required' => false));
                        } elseif (OwnershipType::OWNERSHIP_TYPE_BUSINESS_UNIT == $ownerType) {
                            /**
                             * Showing business unit owner dropdown for entities with owner type Business Unit
                             */
                            if ($changeOwnerGranted) {
                                /**
                                 * If change owner permission is granted, showing all available business units
                                 */
                                $businessUnits = $this->getTreeOptions($this->manager->getBusinessUnitsTree());
                                $builder->add(
                                    $fieldName,
                                    'oro_business_unit_tree_select',
                                    array(
                                        'choices' => $businessUnits,
                                        'mapped' => true,
                                        'required' => false,
                                        'attr' => array('is_safe' => true),
                                    )
                                );
                            } else {
                                $builder->add(
                                    $fieldName,
                                    'entity',
                                    array(
                                        'class' => 'OroOrganizationBundle:BusinessUnit',
                                        'property' => 'name',
                                        'choices' => $user->getBusinessUnits(),
                                        'mapped' => true,
                                        'required' => false,
                                    )
                                );
                            }
                        } elseif (OwnershipType::OWNERSHIP_TYPE_ORGANIZATION == $ownerType) {
                            $fieldOptions = array(
                                'class' => 'OroOrganizationBundle:Organization',
                                'property' => 'name',
                                'mapped' => true,
                                'required' => false,
                            );
                            if (!$changeOwnerGranted) {
                                $organizations = array();
                                $bu = $user->getBusinessUnits();
                                /** @var $businessUnit BusinessUnit */
                                foreach ($bu as $businessUnit) {
                                    $organizations[] = $businessUnit->getOrganization();
                                }
                                $fieldOptions['choices'] = $organizations;
                            }
                            $builder->add(
                                $fieldName,
                                'entity',
                                $fieldOptions
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * Prepare choice options for a hierarchical select
     *
     * @param $options
     * @param int $level
     * @return array
     */
    protected function getTreeOptions($options, $level = 0)
    {
        $choices = array();
        $blanks = str_repeat("&nbsp;&nbsp;&nbsp;", $level);
        foreach ($options as $option) {
            $choices += array($option['id'] => $blanks . $option['name']);
            if (isset($option['children'])) {
                $choices += $this->getTreeOptions($option['children'], $level + 1);
            }
        }

        return $choices;
    }
}