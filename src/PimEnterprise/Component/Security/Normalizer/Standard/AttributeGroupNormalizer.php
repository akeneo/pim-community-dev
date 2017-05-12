<?php

namespace PimEnterprise\Component\Security\Normalizer\Standard;

use Akeneo\Component\Batch\Model\JobInstance;
use PimEnterprise\Bundle\SecurityBundle\Manager\AttributeGroupAccessManager;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * A normalizer to transform an attrribute group entity into a array with permission
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupNormalizer implements NormalizerInterface
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
     * @param JobInstance $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $normalizedAttributeGroup = $this->normalizer->normalize($object, $format, $context);

        $normalizedAttributeGroup['permissions'] = [
            'view' => array_map(function ($permission) {
                return $permission->getName();
            }, $this->accessManager->getViewUserGroups($object)),
            'edit'   => array_map(function ($permission) {
                return $permission->getName();
            }, $this->accessManager->getEditUserGroups($object))
        ];

        return $normalizedAttributeGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $this->normalizer->supportsNormalization($data, $format);
    }
}
