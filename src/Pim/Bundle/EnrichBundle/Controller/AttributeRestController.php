<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Doctrine\ORM\EntityRepository;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Attribute rest controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeRestController
{
    /** @var EntityRepository */
    protected $attributeRepository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var CollectionFilterInterface */
    protected $collectionFilter;

    /** @var ObjectFilterInterface */
    protected $objectFilter;

    /**
     * @param EntityRepository          $attributeRepository
     * @param NormalizerInterface       $normalizer
     * @param CollectionFilterInterface $collectionFilter
     * @param ObjectFilterInterface     $objectFilter
     */
    public function __construct(
        EntityRepository $attributeRepository,
        NormalizerInterface $normalizer,
        CollectionFilterInterface $collectionFilter,
        ObjectFilterInterface $objectFilter
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->normalizer          = $normalizer;
        $this->collectionFilter    = $collectionFilter;
        $this->objectFilter        = $objectFilter;
    }

    /**
     * Get the attribute collection
     *
     * @return JsonResponse
     */
    public function indexAction()
    {
        $attributes = $this->attributeRepository->findAll();
        $filteredAttributes = $this->collectionFilter->filterCollection($attributes, 'pim:internal_api:attribute:view');
        $normalizedAttributes = $this->normalizer->normalize($filteredAttributes, 'json');

        return new JsonResponse($normalizedAttributes);
    }

    /**
     * Get a single attribute
     * @param integer $id
     *
     * @throws NotFoundHttpException If the attribute is not found or the user doesn't have the right to see it
     *
     * @return JsonResponse
     */
    public function getAction($id)
    {
        $attribute = $this->attributeRepository->findOneById($id);

        if (null === $attribute || $this->objectFilter->filterObject($attribute, 'pim:internal_api:attribute:view')) {
            throw new NotFoundHttpException(sprintf('Attribute with id "%s", does not exists', $id));
        }

        return new JsonResponse($this->normalizer->normalize($attribute, 'json'));
    }
}
