<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Model;

/**
 * Define on which fields or attributes actions of a rule can have impacts.
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
interface FieldImpactActionInterface
{
    /**
     * Get the list of fields or attribute impacted by a rule
     *
     * @return array
     */
    public function getImpactedFields();
}
