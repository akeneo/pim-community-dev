<?php

declare(strict_types=1);

namespace Akeneo\Channel\Bundle\Doctrine\Query;

use Akeneo\Channel\Component\Query\FindActivatedCurrenciesInterface;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindActivatedCurrencies implements FindActivatedCurrenciesInterface
{
    /** @var array */
    private $activatedCurrenciesForChannels = [];

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
     * Method that returns a list of currencies codes activated for the given channel.
     *
     * @param string $channelCode
     *
     * @return array
     *
     * @throws DBALException
     */
    public function forChannel(string $channelCode): array
    {
        if (empty($this->activatedCurrenciesForChannels)) {
            $this->activatedCurrenciesForChannels = $this->fetchActivatedCurrenciesForAllChannels();
        }

        return $this->activatedCurrenciesForChannels[$channelCode] ?? [];
    }

    /**
     * {@inheritdoc}
     *
     * @throws DBALException
     */
    public function forAllChannels(): array
    {
        if (empty($this->activatedCurrenciesForChannels)) {
            $this->activatedCurrenciesForChannels = $this->fetchActivatedCurrenciesForAllChannels();
        }

        return array_unique(array_merge(...array_values($this->activatedCurrenciesForChannels)));
    }

    /**
     * @return array
     *
     * @throws DBALException
     */
    private function fetchActivatedCurrenciesForAllChannels(): array
    {
        $sql = <<<SQL
SELECT ch.code as channel_code, JSON_ARRAYAGG(cu.code) as activated_currencies
FROM pim_catalog_channel ch
  INNER JOIN pim_catalog_channel_currency chcu on ch.id = chcu.channel_id
  INNER JOIN pim_catalog_currency cu on chcu.currency_id = cu.id
WHERE cu.is_activated IS TRUE
GROUP BY ch.code;
SQL;
        $statement = $this->entityManager->getConnection()->executeQuery($sql);

        $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $currenciesIndexedByChannel = [];
        foreach ($results as $result) {
            $currenciesIndexedByChannel[$result['channel_code']] = json_decode($result['activated_currencies'], false);
        }

        return $currenciesIndexedByChannel;
    }
}
