<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Controller;

use Pim\Bundle\CatalogBundle\Entity\Channel;
use PimEnterprise\Component\ProductAsset\Repository\ChannelConfigurationRepositoryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Channel transformations controller
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class ChannelTransformationController
{
    /** @var ChannelConfigurationRepositoryInterface */
    protected $transformationRepo;

    /**
     * @param ChannelConfigurationRepositoryInterface $transformationRepo
     */
    public function __construct(ChannelConfigurationRepositoryInterface $transformationRepo)
    {
        $this->transformationRepo = $transformationRepo;
    }

    /**
     * View channel transformations
     *
     * @param Channel $channel
     *
     * @Template
     *
     * @return array
     */
    public function viewAction(Channel $channel)
    {
        return ['channelTransformations' => $this->transformationRepo->findOneByIdentifier($channel->getId())];
    }
}
