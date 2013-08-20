<?php

namespace Oro\Bundle\SecurityBundle\Acl\Domain;

use Symfony\Component\Security\Acl\Model\AclProviderInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Exception\NotAllAclsFoundException;

class RootBasedAclProvider implements AclProviderInterface
{
    /**
     * @var AclProviderInterface
     */
    protected $baseAclProvider;

    /**
     * @var ObjectIdentityFactory
     */
    protected $objectIdentityFactory = null;

    /**
     * Constructor
     *
     * @param ObjectIdentityFactory $objectIdentityFactory
     */
    public function __construct(ObjectIdentityFactory $objectIdentityFactory)
    {
        $this->objectIdentityFactory = $objectIdentityFactory;
    }

    /**
     * Sets the base ACL provider
     *
     * @param AclProviderInterface $provider
     */
    public function setBaseAclProvider(AclProviderInterface $provider)
    {
        $this->baseAclProvider = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function findChildren(ObjectIdentityInterface $parentOid, $directChildrenOnly = false)
    {
        return $this->baseAclProvider->findChildren($parentOid, $directChildrenOnly);
    }

    /**
     * {@inheritdoc}
     */
    public function findAcl(ObjectIdentityInterface $oid, array $sids = array())
    {
        try {
            return $this->baseAclProvider->findAcl($oid, $sids);
        } catch (AclNotFoundException $noAcl) {
            // Try to get ACL for root object
            $rootOid = $this->objectIdentityFactory->root();
            try {
                return $this->baseAclProvider->findAcl($rootOid, $sids);
            } catch (AclNotFoundException $noRootAcl) {
                throw new AclNotFoundException(
                    sprintf('There is no ACL for %s. ACL for %s does not found as well.', $oid, $rootOid),
                    0,
                    $noAcl
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function findAcls(array $oids, array $sids = array())
    {
        return $this->baseAclProvider->findAcls($oids, $sids);
    }
}
