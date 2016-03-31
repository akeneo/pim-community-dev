<?php

namespace Akeneo\Component\Batch\Model;

/**
 * Class ConfigurableInterface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
interface ConfigurableInterface
{
    /**
     * @param Configuration $configuration
     */
    public function configure(Configuration $configuration);
}