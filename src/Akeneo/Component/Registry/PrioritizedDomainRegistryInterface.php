<?php

namespace Akeneo\Component\Registry;

use Akeneo\Component\Registry\Exception\InvalidObjectException;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface PrioritizedDomainRegistryInterface
{
    /**
     * @param int $priority
     * @param object $object
     *
     * @throws InvalidObjectException
     */
    public function register($priority, $object);

    /**
     * @return \SplPriorityQueue
     */
    public function all();

    /**
     * @param object $object
     *
     * @return bool
     */
    public function has($object);

    /**
     * @param object $object
     */
    public function unregister($object);
}
