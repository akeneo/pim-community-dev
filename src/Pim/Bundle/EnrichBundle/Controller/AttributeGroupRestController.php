<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Doctrine\ORM\EntityRepository;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\SecurityContextInterface;
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
    protected $attributeGroupRepo;
    protected $normalizer;
    protected $securityContext;

    public function __construct(
        EntityRepository $attributeGroupRepo,
        NormalizerInterface $normalizer,
        SecurityContextInterface $securityContext
    ) {
        $this->attributeGroupRepo = $attributeGroupRepo;
        $this->normalizer         = $normalizer;
        $this->securityContext    = $securityContext;
    }

    public function indexAction()
    {
        $attributeGroups = $this->attributeGroupRepo->findAll();

        $normalizedAttributes = [];
        foreach ($attributeGroups as $attributeGroup) {
            if ($this->securityContext->isGranted(Attributes::VIEW_ATTRIBUTES, $attributeGroup)) {
                $normalizedAttributes[$attributeGroup->getCode()] = $this->normalizer->normalize($attributeGroup, 'json');
            }
        }

        return new JsonResponse($normalizedAttributes);
    }

    public function getAction($id)
    {
        $attributeGroup = $this->attributeGroupGroupRepo->findOneById($id);

        if (!$this->securityContext->isGranted(Attributes::VIEW_ATTRIBUTES, $attributeGroup)) {
            throw new AccessDeniedHttpException('You are not authorized to see this attribute group');
        }

        return new JsonResponse($this->normalizer->normalize($attributeGroup, 'json'));
    }
}
