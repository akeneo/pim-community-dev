<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;

/**
 * Present two files information side by side
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class FilePresenter implements PresenterInterface
{
    /** @var UrlGeneratorInterface */
    protected $generator;

    /**
     * @param UrlGeneratorInterface $generator
     */
    public function __construct(UrlGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($data, array $change)
    {
        return $data instanceof AbstractProductValue
            && 'media' === $data->getAttribute()->getBackendType()
            && array_key_exists('media', $change);
    }

    /**
     * {@inheritdoc}
     */
    public function present($data, array $change)
    {
        $before = '';
        if ($media = $data->getMedia()) {
            if (null !== $media->getFilename() && null !== $media->getOriginalFilename()) {
                $before = sprintf(
                    '<li class="base file">%s</li>',
                    $this->createFileElement($media->getFilename(), $media->getOriginalFilename())
                );
            }
        }

        $after = '';
        if (isset($change['media']['filename']) && isset($change['media']['originalFilename'])) {
            $after = sprintf(
                '<li class="changed file">%s</li>',
                $this->createFileElement($change['media']['filename'], $change['media']['originalFilename'])
            );
        }

        return sprintf('<ul class="diff">%s%s</ul>', $before, $after);
    }

    /**
     * Create a file element
     *
     * @param string $filename
     * @param string $originalFilename
     *
     * @return string
     */
    protected function createFileElement($filename, $originalFilename)
    {
        return sprintf(
            '<i class="icon-file"></i> <a class="no-hash" href="%s">%s</a>',
            $this->generator->generate('pim_enrich_media_show', ['filename' => $filename]),
            $originalFilename
        );
    }
}
