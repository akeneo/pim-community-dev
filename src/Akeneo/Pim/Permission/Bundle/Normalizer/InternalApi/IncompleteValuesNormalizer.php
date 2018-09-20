<?php

namespace Akeneo\Pim\Permission\Bundle\Normalizer\InternalApi;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\IncompleteValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\RequiredValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\IncompleteValuesNormalizer as BaseIncompleteValuesNormalizer;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * This class extends the Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\IncompleteValuesNormalizer
 * of the CE to handle permission of what the current user is able to edit regarding its rights.
 */
class IncompleteValuesNormalizer extends BaseIncompleteValuesNormalizer
{
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /**
     * @param RequiredValueCollectionFactory   $requiredValueCollectionFactory
     * @param IncompleteValueCollectionFactory $incompleteValueCollectionFactory
     * @param AuthorizationCheckerInterface    $authorizationChecker
     */
    public function __construct(
        RequiredValueCollectionFactory $requiredValueCollectionFactory,
        IncompleteValueCollectionFactory $incompleteValueCollectionFactory,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        parent::__construct($requiredValueCollectionFactory, $incompleteValueCollectionFactory);

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
