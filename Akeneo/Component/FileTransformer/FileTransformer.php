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

use Akeneo\Component\FileTransformer\Exception\InvalidFileTransformerOptionsException;
use Akeneo\Component\FileTransformer\Transformation\TransformationRegistry;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * File transformer implementation
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class FileTransformer implements FileTransformerInterface
{
    /** @var TransformationRegistry */
    protected $registry;

    /** @var \Symfony\Component\OptionsResolver\OptionsResolverInterface */
    protected $resolver;

    /**
     * @param TransformationRegistry $registry
     */
    public function __construct(TransformationRegistry $registry)
    {
        $this->registry = $registry;

        $this->resolver = new OptionsResolver();
        $this->resolver->setOptional(['outputFile']);
        $this->resolver->setRequired(['pipeline']);
        $this->resolver->setAllowedTypes(
            ['outputFile' => ['string', 'null'], 'pipeline' => 'array']
        );
        $this->resolver->setDefaults(['outputFile' => null]);
    }

    /**
     * {@inheritdoc}
     */
    public function transform(\SplFileInfo $file, array $transformationPipeline)
    {
        $mimeType = MimeTypeGuesser::getInstance()->guess($file->getPathname());

        foreach ($transformationPipeline as $transformation) {
            $pipelineOptions = $this->resolvePipelineOptions($transformation);

            if (null !== $pipelineOptions['outputFile']) {
                $file = $this->getOutputFile($file, $pipelineOptions['outputFile']);
            }

            foreach ($pipelineOptions['pipeline'] as $name => $options) {
                $transformation = $this->registry->get($name, $mimeType);
                $transformation->transform($file, $options);
            }
        }

        return $this;
    }

    /**
     * Resolves pipeline options
     *
     * @param array $options
     *
     * @throws InvalidFileTransformerOptionsException
     *
     * @return array
     */
    protected function resolvePipelineOptions(array $options)
    {
        try {
            $options = $this->resolver->resolve($options);
        } catch (\Exception $e) {
            throw new InvalidFileTransformerOptionsException(
                'Your options does not fulfil the requirements of the transformation.',
                $e->getCode(),
                $e
            );
        }

        return $options;
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
    protected function getOutputFile(\SplFileInfo $file, $outputFileName)
    {
        $outputFilePath = sprintf('%s/%s', $file->getPath(), $outputFileName);

        // TODO: handle case where the output file already exists
        if (!copy($file->getPathname(), $outputFilePath)) {
            throw new \LogicException(
                sprintf('Copy file from "%s" to "%s" has failed.', $file->getPathname(), $outputFilePath)
            );
        }

        return new \SplFileInfo($outputFilePath);
    }
}
