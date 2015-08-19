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

use Akeneo\Component\FileTransformer\Transformation\TransformationRegistry;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

/**
 * File transformer implementation
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class FileTransformer implements FileTransformerInterface
{
    /** @var TransformationRegistry */
    protected $registry;

    /**
     * @param TransformationRegistry $registry
     */
    public function __construct(TransformationRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function transform(\SplFileInfo $inputFile, array $rawTransformations, $outputFilename = null)
    {
        $mimeType = MimeTypeGuesser::getInstance()->guess($inputFile->getPathname());

        if (null !== $outputFilename) {
            $outputFile = $this->createOutputFile($inputFile, $outputFilename);
        } else {
            $outputFile = $inputFile;
        }

        foreach ($rawTransformations as $name => $options) {
            $transformation = $this->registry->get($name, $mimeType);
            $transformation->transform($outputFile, $options);
        }

        return $outputFile;
    }

    /**
     * TODO: should be wrapped with FlySystem
     * Copies the given file naming it with the output file name parameter and returns it
     *
     * @param \SplFileInfo $file
     * @param string       $outputFileName
     *
     * @throws \LogicException
     *
     * @return \SplFileInfo
     */
    protected function createOutputFile(\SplFileInfo $file, $outputFileName)
    {
        $outputPathname = sprintf('%s/%s', $file->getPath(), $outputFileName);

        // TODO: handle case where the output file already exists
        if (!copy($file->getPathname(), $outputPathname)) {
            throw new \LogicException(
                sprintf('Copy file from "%s" to "%s" has failed.', $file->getPathname(), $outputPathname)
            );
        }

        return new \SplFileInfo($outputPathname);
    }
}
