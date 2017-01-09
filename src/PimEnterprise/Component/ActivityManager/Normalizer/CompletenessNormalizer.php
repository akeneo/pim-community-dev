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
use PimEnterprise\Component\ActivityManager\Model\Completeness;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizes a Completeness value object.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class CompletenessNormalizer implements NormalizerInterface
{
    /** @var array $supportedFormats */
    protected $supportedFormats = ['internal_api'];

    /**
     * {@inheritdoc}
     *
     * returns
     * [
     *     'isComplete' => (bool),
     *     'productsNumberTodo' => (int),
     *     'productsNumberInProgress' => (int),
     *     'productsNumberDone' => (int),
     *     'completenessTodo' => (int),
     *     'completenessTodo' => (int),
     *     'completenessInProgress' => (int),
     *     'completenessDone' => (int),
     * ]
     */
    public function normalize($completeness, $format = null, array $context = [])
    {
        if (!$completeness instanceof Completeness) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "%s", "%s" provided.',
                    Completeness::class,
                    ClassUtils::getClass($completeness)
                )
            );
        }

        return [
            'isComplete' => $completeness->isComplete(),
            'productsNumberTodo' => $completeness->getProductsNumberForTodo(),
            'productsNumberInProgress' => $completeness->getProductsNumberForInProgress(),
            'productsNumberDone' => $completeness->getProductsNumberForDone(),
            'completenessTodo' => $completeness->getCompletenessForTodo(),
            'completenessInProgress' => $completeness->getCompletenessForInProgress(),
            'completenessDone' => $completeness->getCompletenessForDone(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($completeness, $format = null)
    {
        return $completeness instanceof Completeness && in_array($format, $this->supportedFormats);
    }
}
