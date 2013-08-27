<?php

namespace Oro\Bundle\SecurityBundle\Acl\Extension;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Oro\Bundle\EntityBundle\ORM\EntityClassAccessor;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdAccessor;
use Oro\Bundle\SecurityBundle\Acl\Extension\OwnershipDecisionMakerInterface;
use Oro\Bundle\EntityBundle\ORM\EntityClassResolver;
use Oro\Bundle\EntityBundle\Owner\Metadata\OwnershipMetadataProvider;
use Oro\Bundle\EntityBundle\Owner\Metadata\OwnershipMetadata;
use Oro\Bundle\SecurityBundle\Acl\Exception\InvalidAclMaskException;

class EntityAclExtension extends AbstractAclExtension
{
    /**
     * @var EntityClassAccessor
     */
    protected $entityClassAccessor;

    /**
     * @var ObjectIdAccessor
     */
    protected $objectIdAccessor;

    /**
     * @var EntityClassResolver
     */
    protected $entityClassResolver;

    /**
     * @var OwnershipMetadataProvider
     */
    protected $metadataProvider;

    /**
     * @var OwnershipDecisionMakerInterface
     */
    protected $decisionMaker;

    /**
     * Constructor
     *
     * @param EntityClassAccessor $entityClassAccessor
     * @param ObjectIdAccessor $objectIdAccessor
     * @param EntityClassResolver $entityClassResolver
     * @param OwnershipMetadataProvider $metadataProvider
     * @param OwnershipDecisionMakerInterface $decisionMaker
     */
    public function __construct(
        EntityClassAccessor $entityClassAccessor,
        ObjectIdAccessor $objectIdAccessor,
        EntityClassResolver $entityClassResolver,
        OwnershipMetadataProvider $metadataProvider,
        OwnershipDecisionMakerInterface $decisionMaker
    ) {
        $this->entityClassAccessor = $entityClassAccessor;
        $this->objectIdAccessor = $objectIdAccessor;
        $this->entityClassResolver = $entityClassResolver;
        $this->metadataProvider = $metadataProvider;
        $this->decisionMaker = $decisionMaker;

        $this->map = array(
            'VIEW' => array(
                EntityMaskBuilder::MASK_VIEW_BASIC,
                EntityMaskBuilder::MASK_VIEW_LOCAL,
                EntityMaskBuilder::MASK_VIEW_DEEP,
                EntityMaskBuilder::MASK_VIEW_GLOBAL,
            ),
            'CREATE' => array(
                EntityMaskBuilder::MASK_CREATE_BASIC,
                EntityMaskBuilder::MASK_CREATE_LOCAL,
                EntityMaskBuilder::MASK_CREATE_DEEP,
                EntityMaskBuilder::MASK_CREATE_GLOBAL,
            ),
            'EDIT' => array(
                EntityMaskBuilder::MASK_EDIT_BASIC,
                EntityMaskBuilder::MASK_EDIT_LOCAL,
                EntityMaskBuilder::MASK_EDIT_DEEP,
                EntityMaskBuilder::MASK_EDIT_GLOBAL,
            ),
            'DELETE' => array(
                EntityMaskBuilder::MASK_DELETE_BASIC,
                EntityMaskBuilder::MASK_DELETE_LOCAL,
                EntityMaskBuilder::MASK_DELETE_DEEP,
                EntityMaskBuilder::MASK_DELETE_GLOBAL,
            ),
            'ASSIGN' => array(
                EntityMaskBuilder::MASK_ASSIGN_BASIC,
                EntityMaskBuilder::MASK_ASSIGN_LOCAL,
                EntityMaskBuilder::MASK_ASSIGN_DEEP,
                EntityMaskBuilder::MASK_ASSIGN_GLOBAL,
            ),
            'SHARE' => array(
                EntityMaskBuilder::MASK_SHARE_BASIC,
                EntityMaskBuilder::MASK_SHARE_LOCAL,
                EntityMaskBuilder::MASK_SHARE_DEEP,
                EntityMaskBuilder::MASK_SHARE_GLOBAL,
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports($type, $id)
    {
        if ($type === $this->getRootType()) {
            $type = $this->entityClassResolver->getEntityClass($this->entityClassAccessor->getClass($id));
            $id = null;
        } else {
            $type = $this->entityClassAccessor->getClass($type);
        }

        $delim = strrpos($type, '\\');
        if ($delim && $this->entityClassResolver->isKnownEntityClassNamespace(substr($type, 0, $delim))) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getRootType()
    {
        return 'entity';
    }

    /**
     * {@inheritdoc}
     */
    public function validateMask($mask, $object)
    {
        if ($mask === 0) {
            return;
        }

        $validMasks = $this->getValidMasks($object);
        if (($mask | $validMasks) === $validMasks) {
            $this->validateMaskScope($mask, $object, 'VIEW');
            $this->validateMaskScope($mask, $object, 'CREATE');
            $this->validateMaskScope($mask, $object, 'EDIT');
            $this->validateMaskScope($mask, $object, 'DELETE');
            $this->validateMaskScope($mask, $object, 'ASSIGN');
            $this->validateMaskScope($mask, $object, 'SHARE');

            return;
        }

        throw $this->createInvalidAclMaskException($mask, $object);
    }

    /**
     * {@inheritdoc}
     */
    public function createObjectIdentity($object)
    {
        return is_string($object)
            ? $this->fromDescriptor($object)
            : $this->fromDomainObject($object);
    }

    /**
     * {@inheritdoc}
     */
    public function createMaskBuilder()
    {
        return new EntityMaskBuilder();
    }

    /**
     * {@inheritdoc}
     */
    public function decideIsGranting($aceMask, $object, TokenInterface $securityToken)
    {
        if (0 !== ($aceMask & EntityMaskBuilder::GROUP_SYSTEM)) {
            return true;
        }

        // check whether we check permissions for a domain object
        if ($object === null || !is_object($object) || $object instanceof ObjectIdentityInterface) {
            return true;
        }

        $metadata = $this->getMetadata($object);
        if (!$metadata->hasOwner()) {
            return true;
        }

        if (0 !== ($aceMask & EntityMaskBuilder::GROUP_GLOBAL)) {
            return $this->decisionMaker->isBelongToOrganization($securityToken->getUser(), $object);
        } elseif (0 !== ($aceMask & EntityMaskBuilder::GROUP_DEEP)) {
            return $this->decisionMaker->isBelongToBusinessUnit($securityToken->getUser(), $object, true);
        } elseif (0 !== ($aceMask & EntityMaskBuilder::GROUP_LOCAL)) {
            return $this->decisionMaker->isBelongToBusinessUnit($securityToken->getUser(), $object);
        } elseif (0 !== ($aceMask & EntityMaskBuilder::GROUP_BASIC)) {
            return $this->decisionMaker->isBelongToUser($securityToken->getUser(), $object);
        }

        throw new \RuntimeException(
            sprintf(
                'Unexpected ACE mask "%s" for %s.',
                EntityMaskBuilder::getPatternFor($aceMask),
                get_class($object)
            )
        );
    }

    /**
     * Constructs an ObjectIdentity for the given domain object
     *
     * @param string $descriptor
     * @return ObjectIdentity
     * @throws \InvalidArgumentException
     */
    protected function fromDescriptor($descriptor)
    {
        $type = $id = null;
        $this->parseDescriptor($descriptor, $type, $id);

        if ($type === $this->getRootType()) {
            return new ObjectIdentity(
                $this->entityClassResolver->getEntityClass($this->entityClassAccessor->getClass($id)),
                $this->getRootType()
            );
        }

        throw new \InvalidArgumentException(
            sprintf('Unsupported object identity descriptor: %s.', $descriptor)
        );
    }

    /**
     * Constructs an ObjectIdentity for the given domain object
     *
     * @param object $domainObject
     * @return ObjectIdentity
     * @throws InvalidDomainObjectException
     */
    protected function fromDomainObject($domainObject)
    {
        if (!is_object($domainObject)) {
            throw new InvalidDomainObjectException('$domainObject must be an object.');
        }

        try {
            return new ObjectIdentity(
                $this->objectIdAccessor->getId($domainObject),
                $this->entityClassAccessor->getClass($domainObject)
            );
        } catch (\InvalidArgumentException $invalid) {
            throw new InvalidDomainObjectException($invalid->getMessage(), 0, $invalid);
        }
    }

    /**
     * Checks that the given mask represents only one scope
     *
     * @param int $mask
     * @param mixed $object
     * @param $subMaskName
     * @throws InvalidAclMaskException
     */
    protected function validateMaskScope($mask, $object, $subMaskName)
    {
        if (0 !== ($mask & EntityMaskBuilder::getConst('GROUP_' . $subMaskName))) {
            $maskScopes = array();
            foreach (array('SYSTEM', 'GLOBAL', 'DEEP', 'LOCAL', 'BASIC') as $scope) {
                if (0 !== ($mask & EntityMaskBuilder::getConst('MASK_' . $subMaskName . '_' . $scope))) {
                    $maskScopes[] = $scope;
                }
            }
            if (count($maskScopes) > 1) {
                $msg = sprintf(
                    'The %s mask must be in one scope only, but it is in %s scopes.',
                    $subMaskName,
                    implode(', ', $maskScopes)
                );
                throw $this->createInvalidAclMaskException($mask, $object, $msg);
            }
        }
    }

    /**
     * Gets all valid bitmasks for the given object
     *
     * @param mixed $object
     * @return int
     */
    protected function getValidMasks($object)
    {
        $metadata = $this->getMetadata($object);
        if (!$metadata->hasOwner()) {
            return
                EntityMaskBuilder::GROUP_CRUD_SYSTEM;
        } elseif ($metadata->isOrganizationOwned()) {
            return
                EntityMaskBuilder::GROUP_SYSTEM
                | EntityMaskBuilder::GROUP_GLOBAL;
        } elseif ($metadata->isBusinessUnitOwned()) {
            return
                EntityMaskBuilder::GROUP_SYSTEM
                | EntityMaskBuilder::GROUP_GLOBAL
                | EntityMaskBuilder::GROUP_DEEP
                | EntityMaskBuilder::GROUP_LOCAL;
        } elseif ($metadata->isUserOwned()) {
            return
                EntityMaskBuilder::GROUP_SYSTEM
                | EntityMaskBuilder::GROUP_GLOBAL
                | EntityMaskBuilder::GROUP_DEEP
                | EntityMaskBuilder::GROUP_LOCAL
                | EntityMaskBuilder::GROUP_BASIC;
        }

        return 0;
    }

    /**
     * Gets metadata for the given object
     *
     * @param mixed $object
     * @return OwnershipMetadata
     */
    protected function getMetadata($object)
    {
        if ($object instanceof ObjectIdentity) {
            $className = $object->getType();
        } elseif (is_string($object)) {
            $sortOfDescriptor = $className = null;
            $this->parseDescriptor($object, $sortOfDescriptor, $className);
        } else {
            $className = $this->entityClassAccessor->getClass($object);
        }

        return $this->metadataProvider->getMetadata($className);
    }
}
