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
 * Apply a set of transformations on a file
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
interface FileTransformerInterface
{
    /**
     * Apply a set of transformations on the given file with the given configuration
     *
     * $transformationPipeline = [
     *   [
     *        'outputFile' => 'myNewFile.jpg' (string|null optional)
     *        'pipeline'   => [
     *            'colorspace' => ['colorspace' => 'gray'],
     *            'resize'     => ['width' => 400, 'height' => 50]
     *
     *        ]
     *   ],
     *   [
     *        'pipeline'   => [
     *            'resolution' => ['resolution' => 5, 'resolution-unit' => 'ppi']
     *            'colorspace' => ['colorspace' => 'gray'],
     *            'thumbnail'  => ['width' => 100, 'height' => 100]
     *        ]
     *   ]
     * ]
     *
     * @param \SplFileInfo $file
     * @param array        $transformationPipeline
     *
     * @return FileTransformerInterface
     */
    public function transform(\SplFileInfo $file, array $transformationPipeline);
}
