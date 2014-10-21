<?php


namespace Pim\Component\Resource\Domain;

/**
 * Resource set interface
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ResourceSetInterface
{
    /**
     * @return string
     */
    public function getType();

    /**
     * @return ResourceInterface[]
     */
    public function getResources();
}
