<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Normalizer;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectCompleteness;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizes a Completeness value object.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProjectCompletenessNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
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
        return [
            'is_complete'                => $projectCompleteness->isComplete(),
            'products_count_todo'        => $projectCompleteness->getProductsCountTodo(),
            'products_count_in_progress' => $projectCompleteness->getProductsCountInProgress(),
            'products_count_done'        => $projectCompleteness->getProductsCountDone(),
            'ratio_todo'                 => $projectCompleteness->getRatioForTodo(),
            'ratio_in_progress'          => $projectCompleteness->getRatioForInProgress(),
            'ratio_done'                 => $projectCompleteness->getRatioForDone(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($projectCompleteness, $format = null): bool
    {
        return $projectCompleteness instanceof ProjectCompleteness && in_array($format, $this->supportedFormats);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
