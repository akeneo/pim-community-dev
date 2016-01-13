<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Workflow\Provider;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class ProductDraftGrantedAttributeProvider
 *
 * @author Clement Gautier <clement.gautier@akeneo.com>
 */
class ProductDraftGrantedAttributeProvider
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $localeRepository;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /**
     * @param IdentifiableObjectRepositoryInterface $attributeRepository
     * @param IdentifiableObjectRepositoryInterface $localeRepository
     * @param AuthorizationCheckerInterface         $authorizationChecker
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->attributeRepository  = $attributeRepository;
        $this->localeRepository     = $localeRepository;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param ProductDraftInterface $proposal
     *
     * @return array
     */
    public function getViewable(ProductDraftInterface $proposal)
    {
        $attributes = [];
        $values     = $proposal->getChanges()['values'];

        foreach ($values as $code => $changes) {
            $attribute = $this->attributeRepository->findOneByIdentifier($code);

            foreach ($changes as $change) {
                if ($this->isChangeViewable($attribute, $change['locale'])) {
                    $attributes[$code] = $attribute;
                }
            }
        }

        return $attributes;
    }

    /**
     * Check that the current user have view access to the change
     *
     * @param AttributeInterface $attributeCode
     * @param string             $localeCode
     *
     * @return bool
     */
    protected function isChangeViewable(AttributeInterface $attribute, $localeCode)
    {
        if (!$this->authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $attribute)) {
            return false;
        }

        if ($attribute->isLocalizable()) {
            $isLocaleGranted = $this->authorizationChecker->isGranted(
                Attributes::VIEW_ITEMS,
                $this->localeRepository->findOneByIdentifier($localeCode)
            );

            if (!$isLocaleGranted) {
                return false;
            }
        }

        return true;
    }
}
