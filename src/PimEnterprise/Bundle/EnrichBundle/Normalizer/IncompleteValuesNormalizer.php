<?php

namespace PimEnterprise\Bundle\EnrichBundle\Normalizer;

use Pim\Bundle\EnrichBundle\Normalizer\IncompleteValuesNormalizer as BaseIncompleteValuesNormalizer;
use Pim\Component\Catalog\EntityWithFamily\IncompleteValueCollectionFactory;
use Pim\Component\Catalog\EntityWithFamily\RequiredValueCollectionFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\EntityWithFamilyInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * This class extends the Pim\Bundle\EnrichBundle\Normalizer\IncompleteValuesNormalizer
 * of the CE to handle permission of what the current user is able to edit regarding its rights.
 */
class IncompleteValuesNormalizer extends BaseIncompleteValuesNormalizer
{
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /**
     * @param NormalizerInterface              $normalizer
     * @param RequiredValueCollectionFactory   $requiredValueCollectionFactory
     * @param IncompleteValueCollectionFactory $incompleteValueCollectionFactory
     * @param AuthorizationCheckerInterface    $authorizationChecker
     */
    public function __construct(
        NormalizerInterface $normalizer,
        RequiredValueCollectionFactory $requiredValueCollectionFactory,
        IncompleteValueCollectionFactory $incompleteValueCollectionFactory,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        parent::__construct($normalizer, $requiredValueCollectionFactory, $incompleteValueCollectionFactory);

        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    protected function isEntityGranted(EntityWithFamilyInterface $entityWithFamily)
    {
        return $this->authorizationChecker->isGranted(Attributes::EDIT, $entityWithFamily);
    }

    /**
     * {@inheritdoc}
     */
    protected function isAttributeGranted(AttributeInterface $attribute)
    {
        return $this->authorizationChecker->isGranted(Attributes::EDIT_ATTRIBUTES, $attribute->getGroup());
    }

    /**
     * {@inheritdoc}
     */
    protected function isLocaleGranted(LocaleInterface $locale)
    {
        return $this->authorizationChecker->isGranted(Attributes::EDIT_ITEMS, $locale);
    }
}
