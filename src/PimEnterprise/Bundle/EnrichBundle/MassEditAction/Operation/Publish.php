<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation;

use Pim\Bundle\EnrichBundle\MassEditAction\Operation\AbstractMassEditOperation;

/**
 * Batch operation to publish products
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class Publish extends AbstractMassEditOperation
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->setActions([]);
    }

    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return 'pimee_enrich_mass_publish';
    }

    /**
     * {@inheritdoc}
     */
    public function getOperationAlias()
    {
        return 'publish';
    }

    /**
     * {@inheritdoc}
     */
    public function getBatchJobCode()
    {
        return 'publish_product';
    }

    /**
     * {@inheritdoc}
     */
    public function getFormOptions()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getItemsName()
    {
        return 'product';
    }
}
