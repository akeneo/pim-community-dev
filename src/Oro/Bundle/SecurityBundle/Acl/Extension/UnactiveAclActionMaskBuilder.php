<?php

namespace Oro\Bundle\SecurityBundle\Acl\Extension;

use Oro\Bundle\SecurityBundle\Acl\Permission\MaskBuilder;
use Oro\Bundle\SecurityBundle\Acl\Permission\SingleAclMaskBuilderInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

/**
 * A permission mask builder to unactive an ACL by default.
 */
final class UnactiveAclActionMaskBuilder extends MaskBuilder implements SingleAclMaskBuilderInterface
{
    /** @var string */
    private $extensionKey;

    /** @var string */
    private $aclId;

    public function __construct(string $extensionKey, string $aclId)
    {
        parent::__construct();

        $this->extensionKey = $extensionKey;
        $this->aclId = $aclId;
    }

    public function getOid(): ObjectIdentity
    {
        return new ObjectIdentity($this->extensionKey, $this->aclId);
    }

    public function getDefaultMask(): int
    {
        return 0;
    }

    public function getDefaultGranting(): bool
    {
        return true;
    }
}
