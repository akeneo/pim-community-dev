<?php

namespace Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManagerInterface;

/**
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UiChannelRepository implements UiRepositoryInterface
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var string */
    protected $entityName;

    /**
     * @param EntityManagerInterface $entityManager
     * @param string                 $entityName
     */
    public function __construct(EntityManagerInterface $entityManager, $entityName)
    {
        $this->entityManager = $entityManager;
        $this->entityName    = $entityName;
    }

    /**
     * Get channel choices
     * Allow to list channels in an array like array[<code>] = <label>
     *
     * @return string[]
     */
    public function getLabelsIndexedByCode()
    {
        $qb = $this->entityManager->createQueryBuilder()->select('c')->from($this->entityName, 'c');
        $qb->select('c.code, c.label');

        $channels = $qb->getQuery()->getArrayResult();

        $choices = [];
        foreach ($channels as $channel) {
            $choices[$channel['code']] = $channel['label'];
        }

        return $choices;
    }
}
