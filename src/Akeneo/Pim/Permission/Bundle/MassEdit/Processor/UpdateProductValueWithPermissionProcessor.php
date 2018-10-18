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

use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\UpdateProductValueProcessor as BaseProcessor;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertySetterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * It updates a product value but check if the user has right to mass edit the product (if he is the owner).
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class UpdateProductValueWithPermissionProcessor extends BaseProcessor
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /**
     * @param PropertySetterInterface       $propertySetter
     * @param ValidatorInterface            $validator
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        PropertySetterInterface $propertySetter,
        ValidatorInterface $validator,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        parent::__construct($propertySetter, $validator);

        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function process($product)
    {
        if ($this->authorizationChecker->isGranted(Attributes::OWN, $product)) {
            return BaseProcessor::process($product);
        } else {
            $this->stepExecution->addWarning(
                'pim_enrich.mass_edit_action.edit_common_attributes.message.error',
                [],
                new DataInvalidItem($product)
            );
            $this->stepExecution->incrementSummaryInfo('skipped_products');

            return null;
        }
    }
}
