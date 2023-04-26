<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Exception;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TwoWayAssociationWithTheSameProductException extends \LogicException
{
    const TWO_WAY_ASSOCIATIONS_ERROR_MESSAGE = 'A 2-way association only allows two different products or product models to be associated';
    const TWO_WAY_ASSOCIATIONS_HELP_URL = 'https://help.akeneo.com/pim/serenity/articles/manage-your-association-types.html#create-a-2-way-association-type';

    public function __construct()
    {
        parent::__construct(self::TWO_WAY_ASSOCIATIONS_ERROR_MESSAGE);
    }
}
