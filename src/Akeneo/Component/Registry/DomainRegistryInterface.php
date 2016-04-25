<?php

namespace Akeneo\Component\Registry;

use Akeneo\Component\Registry\Exception\ExistingObjectException;
use Akeneo\Component\Registry\Exception\InvalidObjectException;
use Akeneo\Component\Registry\Exception\NonExistingObjectException;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface DomainRegistryInterface
{
    /**
     * @param string $alias
     * @param object $object
     *
     * @throws InvalidObjectException
     * @throws ExistingObjectException
     */
    public function register($alias, $object);

    /**
     * @return object[]
     */
    public function all();

    /**
     * @param mixed $alias
     *
     * @return bool
     */
    public function has($alias);

    /**
     * @param mixed $alias
     *
     * @throws NonExistingObjectException
     *
     * @return object
     */
    public function get($alias);

    /**
     * @param mixed $alias
     */
    public function unregister($alias);
}
