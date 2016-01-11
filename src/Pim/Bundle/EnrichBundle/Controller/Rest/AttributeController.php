<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Akeneo\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Attribute rest controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeController
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var SearchableRepositoryInterface */
    protected $attributeSearchRepository;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /**
     * @param AttributeRepositoryInterface  $attributeRepository
     * @param NormalizerInterface           $normalizer
     * @param TokenStorageInterface         $tokenStorage
     * @param SearchableRepositoryInterface $attributeSearchRepository
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        NormalizerInterface $normalizer,
        TokenStorageInterface $tokenStorage,
        SearchableRepositoryInterface $attributeSearchRepository = null
    ) {
        $this->attributeRepository       = $attributeRepository;
        $this->normalizer                = $normalizer;
        $this->tokenStorage              = $tokenStorage;
        $this->attributeSearchRepository = $attributeSearchRepository;
    }

    /**
     * Get the attribute collection
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function indexAction(Request $request)
    {
        $options = [];
        if ($request->query->has('identifiers')) {
            $options['identifiers'] = explode(',', $request->query->get('identifiers'));
        }

        if ($request->query->has('types')) {
            $options['types'] = explode(',', $request->query->get('types'));
        }
        if (empty($options)) {
            $options = $request->query->get(
                'options',
                ['limit' => SearchableRepositoryInterface::FETCH_LIMIT, 'locale' => null]
            );
        }

        $token = $this->tokenStorage->getToken();
        $options['user_groups_ids'] = implode(', ', $token->getUser()->getGroupsIds());

        if (null !== $this->attributeSearchRepository) {
            $attributes = $this->attributeSearchRepository->findBySearch(
                $request->query->get('search'),
                $options
            );
        } else {
            if (isset($options['identifiers'])) {
                $options['code'] = $options['identifiers'];
            }
            $attributes = $this->attributeRepository->findBy($options);
        }

        $normalizedAttributes = $this->normalizer->normalize($attributes, 'internal_api');

        return new JsonResponse($normalizedAttributes);
    }

    /**
     * Get attribute by identifier
     *
     * @param string $identifier
     *
     * @return JsonResponse
     */
    public function getAction($identifier)
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($identifier);

        if (null === $attribute) {
            throw new NotFoundHttpException(sprintf('Attribute with code "%s" not found', $identifier));
        }

        return new JsonResponse($this->normalizer->normalize($attribute, 'internal_api'));
    }
}
