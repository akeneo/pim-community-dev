<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\TransformBundle\Normalizer\Flat;

use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Manager\CategoryAccessManager;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Flat category normalizer
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class CategoryNormalizer implements NormalizerInterface
{
    /** @var array */
    protected $supportedFormats = ['csv'];

    /** @var NormalizerInterface */
    protected $categoryNormalizer;

    /** @var CategoryAccessManager */
    protected $accessManager;

    /**
     * @param NormalizerInterface   $categoryNormalizer
     * @param CategoryAccessManager $accessManager
     */
    public function __construct(NormalizerInterface $categoryNormalizer, CategoryAccessManager $accessManager)
    {
        $this->categoryNormalizer = $categoryNormalizer;
        $this->accessManager      = $accessManager;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($category, $format = null, array $context = [])
    {
        $normalizedCategory = $this->categoryNormalizer->normalize($category, $format, $context);

        if (true === $context['versioning']) {
            $normalizedCategory['view_permission'] = implode(
                array_map('strval', $this->accessManager->getViewUserGroups($category)),
                ','
            );
            $normalizedCategory['edit_permission'] = implode(
                array_map('strval', $this->accessManager->getEditUserGroups($category)),
                ','
            );
            $normalizedCategory['own_permission'] = implode(
                array_map('strval', $this->accessManager->getOwnUserGroups($category)),
                ','
            );
        }

        return $normalizedCategory;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof CategoryInterface && in_array($format, $this->supportedFormats);
    }
}
