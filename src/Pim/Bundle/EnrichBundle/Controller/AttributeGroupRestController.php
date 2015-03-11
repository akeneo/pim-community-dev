<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Attribute group controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupRestController
{
    protected $attributeGroupRepository;
    protected $normalizer;

    public function __construct(EntityRepository $attributeGroupRepository, NormalizerInterface $normalizer)
    {
        $this->attributeGroupRepository = $attributeGroupRepository;
        $this->normalizer          = $normalizer;
    }

    public function indexAction()
    {
        $attributes = $this->attributeGroupRepository->findAll();

        $normalizedAttributes = [];
        foreach ($attributes as $attribute) {
            $normalizedAttributes[$attribute->getCode()] = $this->normalizer->normalize($attribute, 'json');
        }

        return new JsonResponse($normalizedAttributes);
    }

    public function getAction($id)
    {
        $attribute = $this->attributeGroupRepository->findOneById($id);

        return new JsonResponse($this->normalizer->normalize($attribute, 'json'));
    }
}
