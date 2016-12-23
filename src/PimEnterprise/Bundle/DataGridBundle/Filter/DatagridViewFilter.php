<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\DataGridBundle\Filter;

use Pim\Bundle\DataGridBundle\Entity\DatagridView;
use PimEnterprise\Bundle\CatalogBundle\Filter\AbstractAuthorizationFilter;
use PimEnterprise\Component\Security\Attributes;

/**
 * Datagrid View filter to only keep views the current user has access to.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DatagridViewFilter extends AbstractAuthorizationFilter
{
    /**
     * {@inheritdoc}
     */
    public function filterCollection($collection, $type, array $options = [])
    {
        return array_values(parent::filterCollection($collection, $type, $options));
    }

    /**
     * {@inheritdoc}
     */
    public function filterObject($view, $type, array $options = [])
    {
        if (!$this->supportsObject($view, $type, $options)) {
            throw new \LogicException('This filter only handles objects of type "DatagridView"');
        }

        return !$this->authorizationChecker->isGranted(Attributes::VIEW, $view);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsObject($object, $type, array $options = [])
    {
        return $object instanceof DatagridView && parent::supportsObject($object, $type, $options);
    }
}
