<?php

/*
* This file is part of the Akeneo PIM Enterprise Edition.
*
* (c) 2015 Akeneo SAS (http://www.akeneo.com)
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Akeneo\Component\FileTransformer;

/**
 * Allow to apply a transformation on a file
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
interface FileTransformerInterface
{
    /**
     * Allows to apply a transformation on the given file with the given configuration
     *
     * $rawConfiguration
     *
     * @param \SplFileInfo $file
     * @param array        $rawTransformations
     *
     * @return FileTransformerInterface
     */
    public function transform(\SplFileInfo $file, array $rawTransformations);
}
