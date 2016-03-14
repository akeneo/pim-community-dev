<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Component\Catalog\Builder\ProductTemplateBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\GroupRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupAttributeController
{
    /** @var GroupRepositoryInterface */
    protected $groupRepository;

    /** @var SaverInterface */
    protected $groupSaver;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var ProductTemplateBuilderInterface */
    protected $templateBuilder;

    /**
     * @param GroupRepositoryInterface        $groupRepository
     * @param SaverInterface                  $groupSaver
     * @param AttributeRepositoryInterface    $attributeRepository
     * @param ProductTemplateBuilderInterface $templateBuilder
     */
    public function __construct(
        GroupRepositoryInterface $groupRepository,
        SaverInterface $groupSaver,
        AttributeRepositoryInterface $attributeRepository,
        ProductTemplateBuilderInterface $templateBuilder
    ) {
        $this->groupRepository     = $groupRepository;
        $this->groupSaver          = $groupSaver;
        $this->attributeRepository = $attributeRepository;
        $this->templateBuilder     = $templateBuilder;
    }

    /**
     * Remove an attribute form a variant group
     *
     * @param int $id          The variant group id
     * @param int $attributeId The attribute id
     *
     * @AclAncestor("pim_enrich_group_remove_attribute")
     *
     * @throws NotFoundHttpException If variant group or attribute is not found or the user cannot see it
     *
     * @return JsonResponse
     */
    public function removeAttributeAction($id, $attributeId)
    {
        $group     = $this->findVariantGroupOr404($id);
        $attribute = $this->findAttributeOr404($attributeId);

        $template = $group->getProductTemplate();
        if (null !== $template) {
            $this->templateBuilder->removeAttribute($template, $attribute);
            $this->groupSaver->save($group);
        }

        return new JsonResponse();
    }

    /**
     * Find a variant group by its id or return a 404 response
     *
     * @param int $id
     *
     * @throws NotFoundHttpException
     *
     * @return GroupInterface
     */
    protected function findVariantGroupOr404($id)
    {
        $group = $this->groupRepository->find($id);

        if (null === $group || false === $group->getType()->isVariant()) {
            throw new NotFoundHttpException(
                sprintf('Variant group with id %d could not be found.', $id)
            );
        }

        return $group;
    }

    /**
     * Find an attribute by its id or return a 404 response
     *
     * @param int $id
     *
     * @throws NotFoundHttpException
     *
     * @return AttributeInterface
     */
    protected function findAttributeOr404($id)
    {
        $attribute = $this->attributeRepository->find($id);

        if (null === $attribute) {
            throw new NotFoundHttpException(
                sprintf('Attribute with id %s could not be found.', $id)
            );
        }

        return $attribute;
    }
}
