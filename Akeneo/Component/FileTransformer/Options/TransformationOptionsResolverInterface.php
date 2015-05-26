<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Component\FileTransformer\Options;

/**
 * Resolve transformation options
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
interface TransformationOptionsResolverInterface
{
    /**
     * Returns the combination of the default and the passed options.
     *
     * @param array $options The custom option values.
     *
     * @throws \Akeneo\Component\FileTransformer\Exception\InvalidOptionsTransformationException
     *
     * @return array A list of options and their values.
     */
    public function resolve(array $options);
}
