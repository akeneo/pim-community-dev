<?php

namespace Oro\Bundle\OrganizationBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Translation\TranslatorInterface;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\OrganizationBundle\Event\RecordOwnerDataListener;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\OrganizationBundle\Entity\Manager\BusinessUnitManager;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\OrganizationBundle\Form\EventListener\OwnerFormSubscriber;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProvider;

class OwnerFormExtension extends AbstractTypeExtension
{
    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @var OwnershipMetadataProvider
     */
    protected $ownershipMetadataProvider;

    /**
     * @var BusinessUnitManager
     */
    protected $businessUnitManager;

    /**
     * @var SecurityFacade
     */
    protected $securityFacade;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var string
     */
    protected $fieldName;

    /**
     * @var string
     */
    protected $fieldLabel = 'Owner';

    /**
     * @var bool
     */
    protected $isAssignGranted;

    /**
     * @var User
     */
    protected $currentUser;

    /**
     * @param SecurityContextInterface $securityContext
     * @param ManagerRegistry $managerRegistry
     * @param OwnershipMetadataProvider $ownershipMetadataProvider
     * @param BusinessUnitManager $businessUnitManager
     * @param SecurityFacade $securityFacade
     * @param TranslatorInterface $translator
     */
    public function __construct(
        SecurityContextInterface $securityContext,
        ManagerRegistry $managerRegistry,
        OwnershipMetadataProvider $ownershipMetadataProvider,
        BusinessUnitManager $businessUnitManager,
        SecurityFacade $securityFacade,
        TranslatorInterface $translator
    ) {
        $this->securityContext = $securityContext;
        $this->managerRegistry = $managerRegistry;
        $this->ownershipMetadataProvider = $ownershipMetadataProvider;
        $this->businessUnitManager = $businessUnitManager;
        $this->securityFacade = $securityFacade;
        $this->translator = $translator;
        $this->fieldName = RecordOwnerDataListener::OWNER_FIELD_NAME;
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

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @throws \LogicException when getOwner method isn't implemented for entity with ownership type
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $dataClassName = $builder->getFormConfig()->getDataClass();
        if (!$dataClassName) {
            return;
        }

        $user = $this->getCurrentUser();
        if (!$user) {
            return;
        }

        $metadata = $this->ownershipMetadataProvider->getMetadata($dataClassName);
        if (!$metadata->hasOwner()) {
            return;
        }

        if (!method_exists($dataClassName, 'getOwner')) {
            throw new \LogicException(
                sprintf('Method getOwner must be implemented for %s entity.', $dataClassName)
            );
        }

        /**
         * TODO: Implement object-based assign check after access levels are supported
         */
        $this->isAssignGranted = $this->securityFacade->isGranted('ASSIGN', 'entity:' . $dataClassName);

        $defaultOwner = null;

        if ($metadata->isUserOwned() && $this->isAssignGranted) {
            $this->addUserOwnerField($builder);
            $defaultOwner = $user;
        } elseif ($metadata->isBusinessUnitOwned()) {
            $this->addBusinessUnitOwnerField($builder, $user, $dataClassName);
            $defaultOwner = $this->getCurrentBusinessUnit();
        } elseif ($metadata->isOrganizationOwned()) {
            $this->addOrganizationOwnerField($builder, $user);
            $defaultOwner = $this->getCurrentOrganization();
        }

        /**
         * Adding subscriber to hide owner field for update pages if assign permission is not granted
         * and set default owner value
         */
        $builder->addEventSubscriber(
            new OwnerFormSubscriber(
                $this->managerRegistry,
                $this->fieldName,
                $this->fieldLabel,
                $this->isAssignGranted,
                $defaultOwner
            )
        );
    }

    /**
     * @param FormBuilderInterface $builder
     */
    protected function addUserOwnerField(FormBuilderInterface $builder)
    {
        $builder->add(
            $this->fieldName,
            'oro_user_select',
            array(
                'required' => true,
                'constraints' => array(new NotBlank())
            )
        );
    }

    /**
     * @param FormBuilderInterface $builder
     * @param User $user
     * @param string $className
     */
    protected function addBusinessUnitOwnerField(FormBuilderInterface $builder, User $user, $className)
    {
        /**
         * Owner field is required for all entities except business unit
         */
        $businessUnitClass = 'Oro\Bundle\OrganizationBundle\Entity\BusinessUnit';
        if ($className != $businessUnitClass && !is_subclass_of($className, $businessUnitClass)) {
            $validation = array(
                'constraints' => array(new NotBlank()),
                'required' => true,
            );
        } else {
            $validation = array(
                'required' => false
            );
            $this->fieldLabel = 'Parent';
        }

        if ($this->isAssignGranted) {
            /**
             * If assign permission is granted, showing all available business units
             */
            $businessUnits = $this->getTreeOptions($this->businessUnitManager->getBusinessUnitsTree());
            $builder->add(
                $this->fieldName,
                'oro_business_unit_tree_select',
                array_merge(
                    array(
                        'empty_value' => $this->translator->trans('oro.business_unit.form.choose_business_user'),
                        'choices' => $businessUnits,
                        'mapped' => true,
                        'attr' => array('is_safe' => true),
                        'label' => $this->fieldLabel
                    ),
                    $validation
                )
            );
        } else {
            $businessUnits = $user->getBusinessUnits();
            if (count($businessUnits)) {
                $builder->add(
                    $this->fieldName,
                    'entity',
                    array_merge(
                        array(
                            'class' => 'OroOrganizationBundle:BusinessUnit',
                            'property' => 'name',
                            'choices' => $businessUnits,
                            'mapped' => true,
                            'label' => $this->fieldLabel
                        ),
                        $validation
                    )
                );
            }
        }
    }

    /**
     * @return null|BusinessUnit
     */
    protected function getCurrentBusinessUnit()
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return null;
        }

        $businessUnits = $user->getBusinessUnits();
        if (!$this->isAssignGranted) {
            return $businessUnits->first();
        }

        // if assign is granted then only allowed business units can be used
        $allowedBusinessUnits = array_keys($this->getTreeOptions($this->businessUnitManager->getBusinessUnitsTree()));

        /** @var BusinessUnit $businessUnit */
        foreach ($businessUnits as $businessUnit) {
            if (in_array($businessUnit->getId(), $allowedBusinessUnits)) {
                return $businessUnit;
            }
        }

        return null;
    }

    /**
     * @return null|User
     */
    protected function getCurrentUser()
    {
        if (null === $this->currentUser) {
            $token = $this->securityContext->getToken();
            if (!$token) {
                $this->currentUser = false;
                return false;
            }

            /** @var User $user */
            $user = $token->getUser();
            if (!$user || is_string($user)) {
                $this->currentUser = false;
                return false;
            }

            $this->currentUser = $user;
        }

        return $this->currentUser;
    }

    /**
     * @return bool|Organization
     */
    protected function getCurrentOrganization()
    {
        $businessUnit = $this->getCurrentBusinessUnit();
        if (!$businessUnit) {
            return true;
        }

        return $businessUnit->getOrganization();
    }

    /**
     * @param FormBuilderInterface $builder
     * @param User $user
     */
    protected function addOrganizationOwnerField(FormBuilderInterface $builder, User $user)
    {
        $fieldOptions = array(
            'class' => 'OroOrganizationBundle:Organization',
            'property' => 'name',
            'mapped' => true,
            'required' => true,
            'constraints' => array(new NotBlank())
        );
        if (!$this->isAssignGranted) {
            $organizations = array();
            $bu = $user->getBusinessUnits();
            /** @var $businessUnit BusinessUnit */
            foreach ($bu as $businessUnit) {
                $organizations[] = $businessUnit->getOrganization();
            }
            $fieldOptions['choices'] = $organizations;
        }
        $builder->add($this->fieldName, 'entity', $fieldOptions);
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
