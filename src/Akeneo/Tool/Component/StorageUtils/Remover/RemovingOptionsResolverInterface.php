<?php

namespace Akeneo\Tool\Component\StorageUtils\Remover;

/**
 * Resolve removing options for single or bulk remove
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface RemovingOptionsResolverInterface
{
    /**
     * Resolve options for a single remove
     *
     * @param array $options
     *
     * @return array
     */
    public function resolveRemoveOptions(array $options);

    /**
     * Resolve options for a bulk remove
     *
     * @param array $options
     *
     * @return array
     */
    public function resolveRemoveAllOptions(array $options);
}
