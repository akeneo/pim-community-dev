<?php

/**
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
     * Apply a set of transformations on the given input file with the given configuration
     * and store it in the output filename.
     * The output file is created at the same location than the input file.
     * If no output filename is provided, the transformations are done on the input file directly.
     *
     * The file where transformations have been applied is returned.
     *
     * $rawTransformations =
     *   [
     *         'colorspace' => ['colorspace' => 'gray'],
     *         'resize'     => ['width' => 400, 'height' => 50]
     *   ]
     *
     * @param \SplFileInfo $inputFile
     * @param array        $rawTransformations
     * @param string       $outputFilename
     *
     * @return \SplFileInfo The file where transformations have been applied
     */
    public function transform(\SplFileInfo $inputFile, array $rawTransformations, $outputFilename = null);
}
