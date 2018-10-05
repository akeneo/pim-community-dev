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
    /** @var ExecutableFinder */
    protected $executableFinder;

    /** @var string */
    protected $defaultConvertPath;

    /**
     * @param string $defaultConvertPath
     */
    public function __construct($defaultConvertPath = null)
    {
        $this->executableFinder = new ExecutableFinder();
        $this->defaultConvertPath = $defaultConvertPath;
    }

    /**
     * Generally speaking a setting should come before an image filename and an image operator after the image filename.
     *
     * @link https://www.imagemagick.org/discourse-server/viewtopic.php?t=6829
     * @link http://magick.imagemagick.org/script/command-line-processing.php#setting
     *
     * @param string $outputOptions
     * @param string $pathName
     * @param string $inputOptions
     *
     * @throws GenericTransformationException
     */
    public function convert($outputOptions, $pathName, $inputOptions = '')
    {
        $output = [];
        $status = null;
        $cmd = sprintf(
            '%s %s %s %s %s 2>&1',
            $this->getConvertBinaryPath(),
            $inputOptions,
            escapeshellarg($pathName),
            $outputOptions,
            escapeshellarg($pathName)
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
