<?php

namespace Akeneo\Component\StorageUtils\Saver;

/**
 * Resolve saving options for single or bulk save
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @internal
 */
interface SavingOptionsResolverInterface
{
    /**
     * Resolve options for a single save
     *
     * @param array $options
     *
     * @return array
     *
     * @internal
     */
    public function resolveSaveOptions(array $options);

    /**
     * Resolve options for a bulk save
     *
     * @param array $options
     *
     * @return array
     *
     * @internal
     */
    public function resolveSaveAllOptions(array $options);
}
