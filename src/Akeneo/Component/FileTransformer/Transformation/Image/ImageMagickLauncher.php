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

use Akeneo\Component\FileTransformer\Exception\NotApplicableTransformation\GenericTransformationException;
use Symfony\Component\Process\ExecutableFinder;

/**
 * Simple Image Magick launcher as we can't use php5-imagick which is not available on Red Hat/CentOs.
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class ImageMagickLauncher
{
    /** @var string */
    protected $defaultConvertPath;

    /** @var ExecutableFinder */
    protected $executableFinder;

    /**
     * @param string $defaultConvertPath
     */
    public function __construct($defaultConvertPath = null)
    {
        $this->executableFinder   = new ExecutableFinder();
        $this->defaultConvertPath = $defaultConvertPath;
    }

    /**
     * @param string $command
     * @param string $pathName
     *
     * @throws GenericTransformationException
     */
    public function convert($command, $pathName)
    {
        $output = [];
        $status = null;
        $cmd    = sprintf(
            '%s %s %s %s',
            $this->getConvertBinaryPath(),
            $command,
            $pathName,
            $pathName
        );

        exec($cmd, $output, $status);

        if (0 !== $status) {
            throw new GenericTransformationException(implode(',', $output));
        }
    }

    /**
     * Get the CONVERT tool path
     *
     * @throws \RuntimeException in case convert is not installed
     *
     * @return string
     */
    public function getConvertBinaryPath()
    {
        $convertPath = $this->executableFinder->find('convert', $this->defaultConvertPath);
        if (null === $convertPath) {
            throw new \RuntimeException('Unable to find Image Magick command line tool "convert".');
        }

        return $convertPath;
    }
}
