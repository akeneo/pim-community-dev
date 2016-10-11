<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Component\Normalizer;

use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a Project from an object to an array. Uses to expose Project to front-end for example.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class ProjectNormalizer implements NormalizerInterface
{
    /** @var array $supportedFormats */
    protected $supportedFormats = ['internal_api'];

    /**
     * {@inheritdoc}
     *
     * returns
     * [
     *     'label' => (string),
     *     'description' => (string),
     *     'due_date' => (string),
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

        $dueDate = $project->getDueDate();
        if (null !== $dueDate) {
            $dueDate = $dueDate->format('YYYY-MM-dd');
        }

        return [
            'label' => $project->getLabel(),
            'description' => $project->getDescription(),
            'due_date' => $dueDate,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($project, $format = null)
    {
        return $project instanceof ProjectInterface && in_array($format, $this->supportedFormats);
    }
}
