<?php

namespace Oro\Bundle\SecurityBundle\Acl\Domain;

use Symfony\Component\Security\Acl\Model\AclInterface;
use Symfony\Component\Security\Acl\Model\EntryInterface;
use Symfony\Component\Security\Acl\Model\PermissionGrantingStrategyInterface;

class RootBasedAclWrapper implements AclInterface
{
    /**
     * @var AclInterface
     */
    private $acl;

    /**
     * @var AclInterface
     */
    private $rootAcl;

    /**
     * @var PermissionGrantingStrategyInterface
     */
    private $permissionGrantingStrategy;

    public function __construct(AclInterface $acl, AclInterface $rootAcl)
    {
        $this->acl = $acl;
        $this->rootAcl = $rootAcl;

        $r = new \ReflectionClass(get_class($this->acl));
        $p = $r->getProperty('permissionGrantingStrategy');
        $p->setAccessible(true);
        $this->permissionGrantingStrategy = $p->getValue($this->acl);
    }

    /**
     * {@inheritdoc}
     */
    public function getClassAces()
    {
        /** @var EntryInterface[] $aces */
        $aces = $this->acl->getClassAces();
        /** @var EntryInterface[] $rootAces */
        $rootAces = $this->rootAcl->getClassAces();

        foreach ($rootAces as $rootAce) {
            $exists = false;
            $rootSid = $rootAce->getSecurityIdentity();
            foreach ($aces as $ace) {
                if ($rootSid->equals($ace->getSecurityIdentity())) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $aces[] = $rootAce;
            }
        }

        return $aces;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassFieldAces($field)
    {
        /** @var EntryInterface[] $aces */
        $aces = $this->acl->getClassFieldAces($field);
        /** @var EntryInterface[] $rootAces */
        $rootAces = $this->rootAcl->getClassFieldAces($field);

        foreach ($rootAces as $rootAce) {
            $exists = false;
            $rootSid = $rootAce->getSecurityIdentity();
            foreach ($aces as $ace) {
                if ($rootSid->equals($ace->getSecurityIdentity())) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $aces[] = $rootAce;
            }
        }

        return $aces;
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectAces()
    {
        /** @var EntryInterface[] $aces */
        $aces = $this->acl->getObjectAces();
        /** @var EntryInterface[] $rootAces */
        $rootAces = $this->rootAcl->getObjectAces();

        foreach ($rootAces as $rootAce) {
            $exists = false;
            $rootSid = $rootAce->getSecurityIdentity();
            foreach ($aces as $ace) {
                if ($rootSid->equals($ace->getSecurityIdentity())) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $aces[] = $rootAce;
            }
        }

        return $aces;
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectFieldAces($field)
    {
        /** @var EntryInterface[] $aces */
        $aces = $this->acl->getObjectFieldAces($field);
        /** @var EntryInterface[] $rootAces */
        $rootAces = $this->rootAcl->getObjectFieldAces($field);

        foreach ($rootAces as $rootAce) {
            $exists = false;
            $rootSid = $rootAce->getSecurityIdentity();
            foreach ($aces as $ace) {
                if ($rootSid->equals($ace->getSecurityIdentity())) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $aces[] = $rootAce;
            }
        }

        return $aces;
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectIdentity()
    {
        return $this->acl->getObjectIdentity();
    }

    /**
     * {@inheritdoc}
     */
    public function getParentAcl()
    {
        return $this->acl->getParentAcl();
    }

    /**
     * {@inheritdoc}
     */
    public function isEntriesInheriting()
    {
        return $this->acl->isEntriesInheriting();
    }

    /**
     * {@inheritdoc}
     */
    public function isFieldGranted($field, array $masks, array $securityIdentities, $administrativeMode = false)
    {
        return $this->permissionGrantingStrategy
            ->isFieldGranted($this, $field, $masks, $securityIdentities, $administrativeMode);
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted(array $masks, array $securityIdentities, $administrativeMode = false)
    {
        return $this->permissionGrantingStrategy
            ->isGranted($this, $masks, $securityIdentities, $administrativeMode);
    }

    /**
     * {@inheritdoc}
     */
    public function isSidLoaded($securityIdentities)
    {
        return $this->acl->isSidLoaded($securityIdentities);
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        throw new \LogicException('Not supported.');
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        throw new \LogicException('Not supported.');
    }
}
