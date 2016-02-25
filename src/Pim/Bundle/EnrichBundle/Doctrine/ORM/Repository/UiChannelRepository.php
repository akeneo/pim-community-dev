<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UiChannelRepository extends EntityRepository
{
    /**
     * Get channel choices
     * Allow to list channels in an array like array[<code>] = <label>
     *
     * @return string[]
     */
    public function getChannelChoices()
    {
//        $channels = $this->findAll();
//
//        $choices = [];
//        foreach ($channels as $channel) {
//            $choices[$channel->getCode()] = $channel->getLabel();
//        }

//        return $choices;




        $qb = $this->createQueryBuilder('c');
        $qb->addSelect();
    }
}
