<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Component\FileTransformer\Transformation\Image;

use Akeneo\Component\FileTransformer\Exception\NotApplicableTransformation\NotApplicableTransformationException;

/**
 * Simple Imagick launcher as we can't use php5-imagick which is not available on Red Hat/CentOs.
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class ImagickLauncher
{
    /** @var string */
    protected $convertBinaryPath;

    /**
     * @param string $convertBinaryPath
     */
    public function __construct($convertBinaryPath)
    {
        $this->convertBinaryPath  = $convertBinaryPath;
    }

    /**
     * @param string $command
     * @param string $pathName
     *
     * @throws NotApplicableTransformationException
     */
    public function convert($command, $pathName)
    {
        $cmd = sprintf(
            '%s %s %s %s',
            $this->convertBinaryPath,
            $command,
            $pathName,
            $pathName
        );

        $output = [];
        $status = null;

        exec($cmd, $output, $status);

         if (0 !== $status) {
            throw new NotApplicableTransformationException(print_r($output, true));
        }
    }
}
