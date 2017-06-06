<?php

namespace Pim\Bundle\EnrichBundle\Factory;

use Pim\Bundle\EnrichBundle\Entity\SequentialEdit;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Sequential edit entity factory
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SequentialEditFactory
{
    /** @var string */
    protected $sequentialEditClass;

    /**
     * @param string $sequentialEditClass
     */
    public function __construct($sequentialEditClass)
    {
        $this->sequentialEditClass = $sequentialEditClass;
    }

    /**
     * Create and configure a sequential edit instance
     *
     * @param integer[]     $objectSet
     * @param UserInterface $user
     *
     * @return SequentialEdit
     */
    public function create(array $objectSet, UserInterface $user)
    {
        $sequentialEdit = new $this->sequentialEditClass();
        $sequentialEdit->setObjectSet($objectSet);
        $sequentialEdit->setUser($user);

        return $sequentialEdit;
    }
}
