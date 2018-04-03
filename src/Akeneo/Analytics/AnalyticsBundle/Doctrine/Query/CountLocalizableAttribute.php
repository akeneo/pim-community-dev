<?php
declare(strict_types=1);

namespace Pim\Bundle\AnalyticsBundle\Doctrine\Query;

use Doctrine\ORM\EntityManagerInterface;
use Pim\Component\Catalog\Model\AttributeInterface;

/**
 * Return the number of localizable attributes
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CountLocalizableAttribute
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return int
     */
    public function __invoke(): int
    {
        $query = $this->entityManager->createQueryBuilder()
            ->select('COUNT(attribute.id)')
            ->from(AttributeInterface::class, 'attribute')
            ->where('attribute.scopable = 0')
            ->andWhere('attribute.localizable = 1')
            ->getQuery();

        return (int) $query->getSingleScalarResult();
    }
}
