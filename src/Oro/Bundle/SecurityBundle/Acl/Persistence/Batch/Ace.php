<?php

namespace Oro\Bundle\SecurityBundle\Acl\Persistence\Batch;

use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;

class Ace
{
    /**
     * @var SecurityIdentityInterface
     */
    private $sid;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $field;

    /**
     * @var int
     */
    private $mask;

    /**
     * @var bool
     */
    private $granting;

    /**
     * @var string
     */
    private $strategy;

    /**
     * @var bool
     */
    private $replace;

    /**
     * Constructor
     *
     * @param string                    $type
     * @param string|null               $field
     * @param SecurityIdentityInterface $sid
     * @param bool                      $granting
     * @param int                       $mask
     * @param string|null               $strategy
     * @param bool                      $replace
     */
    public function __construct(string $type, ?string $field, SecurityIdentityInterface $sid, bool $granting, int $mask, ?string $strategy, bool $replace)
    {
        $this->type = $type;
        $this->field = $field;
        $this->sid = $sid;
        $this->granting = $granting;
        $this->mask = $mask;
        $this->strategy = $strategy;
        $this->replace = $replace;
    }

    /**
     * Gets the security identity associated with this ACE
     */
    public function getSecurityIdentity(): \Symfony\Component\Security\Acl\Model\SecurityIdentityInterface
    {
        return $this->sid;
    }

    /**
     * Gets this ACE type
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Gets the name of a field
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * Gets the permission mask of this ACE
     */
    public function getMask(): int
    {
        return $this->mask;
    }

    /**
     * Indicates whether this ACE is granting, or denying
     */
    public function isGranting(): bool
    {
        return $this->granting;
    }

    /**
     * Gets the strategy for comparing masks
     *
     * @return string|null
     */
    public function getStrategy(): string
    {
        return $this->strategy;
    }

    /**
     * Indicates whether this ACE should replace existing ACE or not
     */
    public function isReplace(): bool
    {
        return $this->replace;
    }
}
