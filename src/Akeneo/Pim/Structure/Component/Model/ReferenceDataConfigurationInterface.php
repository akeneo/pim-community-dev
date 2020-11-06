<?php

namespace Akeneo\Pim\Structure\Component\Model;

/**
 * Reference data configuration interface
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ReferenceDataConfigurationInterface
{
    const TYPE_SIMPLE = 'simple';
    const TYPE_MULTI = 'multi';

    public function getClass(): string;

    /**
     * @param string $class
     */
    public function setClass(string $class);

    public function getName(): string;

    /**
     * @param string $name
     */
    public function setName(string $name);

    public function getType(): string;

    /**
     * @param string $type
     */
    public function setType(string $type);
}
