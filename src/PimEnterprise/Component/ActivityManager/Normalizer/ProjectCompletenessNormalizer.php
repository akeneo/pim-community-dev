<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Normalizer;

use Doctrine\Common\Util\ClassUtils;
use PimEnterprise\Component\ActivityManager\Model\ProjectCompleteness;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizes a Completeness value object.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProjectCompletenessNormalizer implements NormalizerInterface
{
    /** @var array $supportedFormats */
    protected $supportedFormats = ['internal_api'];

    /**
     * {@inheritdoc}
     *
     * returns
     * [
     *     'isComplete' => (bool),
     *     'productsCountTodo' => (int),
     *     'productsCountInProgress' => (int),
     *     'productsCountDone' => (int),
     *     'ratioTodo' => (int),
     *     'ratioInProgress' => (int),
     *     'ratioDone' => (int),
     * ]
     */
    public function normalize($projectCompleteness, $format = null, array $context = [])
    {
        if (!$projectCompleteness instanceof ProjectCompleteness) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "%s", "%s" provided.',
                    ProjectCompleteness::class,
                    ClassUtils::getClass($projectCompleteness)
                )
            );
        }

        return [
            'isComplete' => $projectCompleteness->isComplete(),
            'productsCountTodo' => $projectCompleteness->getProductsCountTodo(),
            'productsCountInProgress' => $projectCompleteness->getProductsCountInProgress(),
            'productsCountDone' => $projectCompleteness->getProductsCountDone(),
            'ratioTodo' => $projectCompleteness->getRatioForTodo(),
            'ratioInProgress' => $projectCompleteness->getRatioForInProgress(),
            'ratioDone' => $projectCompleteness->getRatioForDone(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($projectCompleteness, $format = null)
    {
        return $projectCompleteness instanceof ProjectCompleteness && in_array($format, $this->supportedFormats);
    }
}
