<?php

namespace Oro\Bundle\SecurityBundle\Acl\Extension;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

abstract class AbstractAclExtension implements AclExtensionInterface
{
    protected $map;

    /**
     * {@inheritdoc}
     */
    public function getMasks($permission)
    {
        return isset($this->map[$permission])
            ? $this->map[$permission]
            : null;
    }

    /**
     * {@inheritdoc}
     */
    public function hasMasks($permission)
    {
        return isset($this->map[$permission]);
    }

    /**
     * {@inheritdoc}
     */
    public function decideIsGranting($aceMask, $object, TokenInterface $securityToken)
    {
        return true;
    }

    /**
     * Gets the sort og the given object identity descriptor
     *
     * @param string $descriptor
     * @return string|null
     */
    protected function getSortOfDescriptor($descriptor)
    {
        $delim = strpos($descriptor, ':');
        return $delim
            ? strtolower(substr($descriptor, 0, $delim))
            : null;
    }

    /**
     * Split the given object identity descriptor
     *
     * @param string $descriptor
     * @param string $sortOfDescriptor [output]
     * @param string $value [output]
     * @throws \InvalidArgumentException
     */
    protected function parseDescriptor($descriptor, &$sortOfDescriptor, &$value)
    {
        $delim = strpos($descriptor, ':');
        if (!$delim) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Incorrect descriptor: %s. Expected IdentifierType:Name.',
                    $descriptor
                )
            );
        }

        $sortOfDescriptor = strtolower(substr($descriptor, 0, $delim));
        $value = trim(substr($descriptor, $delim + 1));
    }
}
