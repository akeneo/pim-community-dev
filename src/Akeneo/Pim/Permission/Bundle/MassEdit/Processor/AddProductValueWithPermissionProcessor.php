<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace Akeneo\Pim\Permission\Bundle\MassEdit\Processor;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\AddProductValueProcessor as BaseProcessor;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyAdderInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * It adds a product value but check if the user has right to mass edit the product (if he is the owner).
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class AddProductValueWithPermissionProcessor extends BaseProcessor
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /**
     * @param PropertyAdderInterface        $propertyAdder
     * @param ValidatorInterface            $validator
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        PropertyAdderInterface $propertyAdder,
        ValidatorInterface $validator,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        parent::__construct($propertyAdder, $validator);
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     *
     * We override parent to initialize the security context
     */
    public function process($product)
    {
        if ($this->hasRight($product)) {
            return BaseProcessor::process($product);
        }

        return null;
    }

    /**
     * @param EntityWithValuesInterface $product
     *
     * @return bool
     */
    protected function hasRight(EntityWithValuesInterface $product)
    {
        $isAuthorized = $this->authorizationChecker->isGranted(Attributes::OWN, $product);

        if (!$isAuthorized) {
            $this->stepExecution->addWarning(
                'pim_enrich.mass_edit_action.edit_common_attributes.message.error',
                [],
                new DataInvalidItem($product)
            );
            $this->stepExecution->incrementSummaryInfo('skipped_products');
        }

        return $isAuthorized;
    }
}
