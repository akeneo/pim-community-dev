<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\SecurityContextInterface;
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
    protected $attributeRepository;
    protected $normalizer;
    protected $securityContext;

    public function __construct(
        EntityRepository $attributeRepository,
        NormalizerInterface $normalizer,
        SecurityContextInterface $securityContext
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->normalizer          = $normalizer;
        $this->securityContext     = $securityContext;
    }

    public function indexAction()
    {
        $attributes = $this->attributeRepository->findAll();

        $normalizedAttributes = $this->normalizer->normalize($attributes, 'json');

        return new JsonResponse($normalizedAttributes);
    }

    public function getAction($id)
    {
        $attribute = $this->attributeRepository->findOneById($id);

        return new JsonResponse($this->normalizer->normalize($attribute, 'json'));
    }
}
