<?php

namespace Pim\Bundle\ProductBundle\Calculator;

use Pim\Bundle\ProductBundle\Manager\LocaleManager;
use Pim\Bundle\ProductBundle\Manager\ChannelManager;
use Pim\Bundle\ProductBundle\Calculator\CompletenessCalculator;

use Doctrine\ORM\EntityManager;

/**
 * Batch launching the calculator
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BatchCompletenessCalculator
{
    /**
     * @var \Pim\Bundle\ProductBundle\Calculator\CompletenessCalculator $completenessCalculator
     */
    protected $completenessCalculator;

    /**
     * @var \Pim\Bundle\ProductBundle\Manager\ChannelManager $channelManager
     */
    protected $channelManager;

    /**
     * @var \Pim\Bundle\Productbundle\Manager\LocaleManager $localeManager
     */
    protected $localeManager;

    /**
     * @var \Doctrine\ORM\EntityManager $entityManager
     */
    protected $entityManager;

    /**
     * Constructor
     * @param CompletenessCalculator $calculator
     * @param ChannelManager $channelManager
     * @param LocaleManager $localeManager
     * @param EntityManager $em
     */
    public function __construct(
        CompletenessCalculator $calculator,
        ChannelManager $channelManager,
        LocaleManager $localeManager,
        EntityManager $em
    ) {
        $this->completenessCalculator = $calculator;
        $this->channelManager         = $channelManager;
        $this->localeManager          = $localeManager;
        $this->entityManager          = $em;
    }

    public function execute()
    {
        $results = $this
            ->entityManager
            ->getRepository('PimProductBundle:Channel')
            ->findPendingCompleteness();

        var_dump(count($results));
    }

    /**
     * Get repository for pending completeness entity
     * @return \Doctrine\ORM\EntityRepository
     */
//     protected function getPendingCompletenessRepository()
//     {
//         return $this->entityManager->getRepository('PimProductBundle:PendingCompleteness');
//     }

//     protected function getPendingFamilies()
//     {
//         $this->getPendingCompletenessRepository()->findBy(array('family' => ))
//     }
}
