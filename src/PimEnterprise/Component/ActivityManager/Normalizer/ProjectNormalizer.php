<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Normalizer;

use Doctrine\Common\Util\ClassUtils;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Normalize a Project from an object to an array. Uses to expose Project to front-end for example.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class ProjectNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    /** @var array $supportedFormats */
    protected $supportedFormats = ['internal_api'];

    /** @var Serializer */
    protected $serializer;

    /**
     * {@inheritdoc}
     *
     * returns
     * [
     *     'label' => (string),
     *     'code' => (string),
     *     'description' => (string),
     *     'due_date' => (string),
     *     'owner' => [] internal_api format,
     *     'channel' => [] internal_api format,
     *     'locale' => [] internal_api format,
     *     'datagridView' => [] internal_api format
     * ]
     */
    public function normalize($project, $format = null, array $context = [])
    {
        if (!$project instanceof ProjectInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "%s", "%s" provided.',
                    ProjectInterface::class,
                    ClassUtils::getClass($project)
                )
            );
        }

        return [
            'label'        => $project->getLabel(),
            'code'         => $project->getCode(),
            'description'  => $project->getDescription(),
            'due_date'     => $project->getDueDate()->format('Y-m-d'),
            'owner'        => $this->serializer->normalize($project->getOwner(), $format, $context),
            'channel'      => $this->serializer->normalize($project->getChannel(), $format, $context),
            'locale'       => $this->serializer->normalize($project->getLocale(), $format, $context),
            'datagridView' => $this->serializer->normalize(
                $project->getDatagridView(),
                $format,
                $context
            ),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($project, $format = null)
    {
        return $project instanceof ProjectInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
}
