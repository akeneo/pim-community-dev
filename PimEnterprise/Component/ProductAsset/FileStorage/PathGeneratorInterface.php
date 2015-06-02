<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\FileStorage;

/**
 * Generates all the path data (sanitized and unique filename, path, pathname and guid) of a file
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
interface PathGeneratorInterface
{
    /**
     * Generate all the path data of a file.
     *
     * For example, a file called "this i#s the é file.txt'" could produce the following output:
     *   [
     *      'guid'      => '2fd4e1c67a2d28fced849ee1bb76e7391b93eb12',
     *      'file_name' => '2fd4e1c67a2d28fced849ee1bb76e7391b93eb12_this_i_s_the___file.txt'
     *      'path'      => '2/f/d/4/',
     *      'path_name' => '2/f/d/4/2fd4e1c67a2d28fced849ee1bb76e7391b93eb12_this_i_s_the___file.txt'
     *
     * @param \SplFileInfo $file
     *
     * @return array
     */
    public function generate(\SplFileInfo $file);
}
