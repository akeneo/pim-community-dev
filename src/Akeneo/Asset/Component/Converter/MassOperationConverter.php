<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Component\Converter;

use Akeneo\Pim\Enrichment\Component\Product\Converter\ConverterInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;

/**
 * Converter for Asset Mass Edit.
 * It changes the behavior for classify_asset mass action, by checking the ACL for asset category list instead of
 * product category list.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class MassOperationConverter implements ConverterInterface
{
    private const CLASSIFY_ASSETS_JOB_CODE = 'classify_assets';
    private const CLASSIFY_ASSETS_ACL = 'pimee_product_asset_category_list';

    /** @var ConverterInterface */
    private $converter;

    /** @var SecurityFacade */
    private $securityFacade;

    /**
     * @param ConverterInterface $converter
     * @param SecurityFacade $securityFacade
     */
    public function __construct(ConverterInterface $converter, SecurityFacade $securityFacade)
    {
        $this->converter = $converter;
        $this->securityFacade = $securityFacade;
    }

    /**
     * {@inheritdoc}
     */
    public function convert(array $operation)
    {
        if ($operation['jobInstanceCode'] === self::CLASSIFY_ASSETS_JOB_CODE) {
            $operation['actions'] = array_filter($operation['actions'], function ($action) {
                if (!isset($action['field'])) {
                    return true;
                }

                if ($action['field'] === 'categories') {
                    return $this->securityFacade->isGranted(self::CLASSIFY_ASSETS_ACL);
                }

                return true;
            });

            return $operation;
        }

        return $this->converter->convert($operation);
    }
}
