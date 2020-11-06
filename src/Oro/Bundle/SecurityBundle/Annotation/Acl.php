<?php

namespace Oro\Bundle\SecurityBundle\Annotation;

/**
 * @Annotation
 * @Target({"METHOD", "CLASS"})
 */
class Acl implements \Serializable
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $permission;

    /**
     * @var string
     */
    private $group;

    /**
     * @var string
     */
    private $label;

    /** @var bool */
    private $isEnabledAtCreation = true;

    /**
     * Constructor
     *
     * @param  array                     $data
     * @throws \InvalidArgumentException
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function __construct(array $data = null)
    {
        if ($data === null) {
            return;
        }

        $this->id = isset($data['id']) ? $data['id'] : null;
        if (empty($this->id) || strpos($this->id, ' ') !== false) {
            throw new \InvalidArgumentException('ACL id must not be empty or contain blank spaces.');
        }

        $this->type = isset($data['type']) ? $data['type'] : null;
        if (empty($this->type)) {
            throw new \InvalidArgumentException(sprintf('ACL type must not be empty. Id: %s.', $this->id));
        }

        $this->permission = isset($data['permission']) ? $data['permission'] : '';
        $this->class = isset($data['class']) ? $data['class'] : '';
        $this->group = isset($data['group_name']) ? $data['group_name'] : '';
        $this->label = isset($data['label']) ? $data['label'] : '';
        $this->isEnabledAtCreation = $data['enabled_at_creation'] ?? true;
    }

    /**
     * Gets id of this ACL annotation
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Gets ACL extension key
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Gets ACL class name
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * Gets ACL permission name
     */
    public function getPermission(): string
    {
        return $this->permission;
    }

    /**
     * Sets ACL permission name
     *
     * @param string $permission
     */
    public function setPermission(string $permission): void
    {
        $this->permission = $permission;
    }

    /**
     * Gets ACL group name
     */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * Gets ACL label name
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    public function isEnabledAtCreation(): bool
    {
        return $this->isEnabledAtCreation;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): string
    {
        return serialize(
            [
                $this->id,
                $this->type,
                $this->class,
                $this->permission,
                $this->group,
                $this->label
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized): void
    {
        list(
            $this->id,
            $this->type,
            $this->class,
            $this->permission,
            $this->group,
            $this->label
            ) = unserialize($serialized);
    }
}
