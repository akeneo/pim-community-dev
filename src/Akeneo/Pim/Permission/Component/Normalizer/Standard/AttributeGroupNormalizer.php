<?php

namespace Akeneo\Pim\Permission\Component\Normalizer\Standard;

use Akeneo\Pim\Permission\Bundle\Manager\AttributeGroupAccessManager;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * A normalizer to transform an attrribute group entity into a array with permission
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var AttributeGroupAccessManager */
    protected $accessManager;

    /**
     * @param NormalizerInterface         $normalizer
     * @param AttributeGroupAccessManager $accessManager
     */
    public function __construct(NormalizerInterface $normalizer, AttributeGroupAccessManager $accessManager)
    {
        $this->normalizer    = $normalizer;
        $this->accessManager = $accessManager;
    }

    /**
     * {@inheritdoc}
     *
     * @param AttributeGroupInterface $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $normalizedAttributeGroup = $this->normalizer->normalize($object, $format, $context);

        $viewUserGroups = array_filter($this->accessManager->getViewUserGroups($object), function (GroupInterface $group) {
            return $group->getType() === Group::TYPE_DEFAULT;
        });
        $editUserGroups = array_filter($this->accessManager->getEditUserGroups($object), function (GroupInterface $group) {
            return $group->getType() === Group::TYPE_DEFAULT;
        });

        $normalizedAttributeGroup['permissions'] = [
            'view' => array_map(function ($permission) {
                return $permission->getName();
            }, $viewUserGroups),
            'edit'   => array_map(function ($permission) {
                return $permission->getName();
            }, $editUserGroups)
        ];

        return $normalizedAttributeGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $this->normalizer->supportsNormalization($data, $format);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return $this->normalizer instanceof CacheableSupportsMethodInterface
            && $this->normalizer->hasCacheableSupportsMethod();
    }
}
