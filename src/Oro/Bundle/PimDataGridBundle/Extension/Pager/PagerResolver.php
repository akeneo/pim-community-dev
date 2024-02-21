<?php

namespace Oro\Bundle\PimDataGridBundle\Extension\Pager;

use Oro\Bundle\DataGridBundle\Extension\Pager\PagerInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * Resolver for pager : determine which pager should be used depending on the grid.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PagerResolver implements PagerResolverInterface
{
    /** @var PagerInterface */
    protected $ormPager;

    /** @var PagerInterface */
    protected $dummyPager;

    /** @var array */
    protected $gridsWithDummyPager;

    /**
     * @param PagerInterface $ormPager
     * @param PagerInterface $dummyPager
     * @param array          $gridsWithDummyPager
     */
    public function __construct(PagerInterface $ormPager, PagerInterface $dummyPager, array $gridsWithDummyPager)
    {
        $this->ormPager = $ormPager;
        $this->dummyPager = $dummyPager;
        $this->gridsWithDummyPager = $gridsWithDummyPager;
    }

    /**
     * @param string $datagridName
     *
     * @throws InvalidConfigurationException
     *
     * @return PagerInterface
     */
    public function getPager($datagridName): PagerInterface
    {
        if (in_array($datagridName, $this->gridsWithDummyPager)) {
            return $this->dummyPager;
        }

        return $this->ormPager;
    }
}
