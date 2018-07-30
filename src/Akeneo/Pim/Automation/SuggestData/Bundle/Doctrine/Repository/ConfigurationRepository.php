<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Bundle\Doctrine\Repository;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\Configuration;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ConfigurationRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\ConfigBundle\Entity\Config;
use Oro\Bundle\ConfigBundle\Entity\ConfigValue;

/**
 * Doctrine implementation of the configuration repository.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
final class ConfigurationRepository implements ConfigurationRepositoryInterface
{
    private const ORO_CONFIG_RECORD_ID = 1;

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
     * {@inheritdoc}
     */
    public function findOneByCode(string $code): ?Configuration
    {
        $oroConfig = $this->getOroConfigRepository()->findOneBy([
            'scopedEntity' => $code,
            'recordId' => static::ORO_CONFIG_RECORD_ID,
        ]);

        if (null === $oroConfig) {
            return null;
        }

        $configurationValues = [];
        foreach ($oroConfig->getValues() as $oroConfigValue) {
            $configurationValues[$oroConfigValue->getName()] = $oroConfigValue->getValue();
        }

        return new Configuration($oroConfig->getEntity(), $configurationValues);
    }

    /**
     * {@inheritdoc}
     */
    public function save(Configuration $configuration): void
    {
        $oroConfig = $this->findOrCreateOroConfig($configuration);

        $this->entityManager->persist($oroConfig);
        $this->entityManager->flush();
    }

    /**
     * @return ObjectRepository
     */
    private function getOroConfigRepository(): ObjectRepository
    {
        return $this->entityManager->getRepository(Config::class);
    }

    /**
     * Retrieves an oro config entity from database or creates a new one, then
     * updates it.
     *
     * @param Configuration $configuration
     *
     * @return Config
     */
    private function findOrCreateOroConfig(Configuration $configuration): Config
    {
        $code = $configuration->getCode();

        $oroConfig = $this->getOroConfigRepository()->findOneBy([
            'scopedEntity' => $code,
            'recordId' => static::ORO_CONFIG_RECORD_ID,
        ]);

        if (null === $oroConfig) {
            $oroConfig = new Config();
            $oroConfig->setEntity($code);
            $oroConfig->setRecordId(static::ORO_CONFIG_RECORD_ID);
        }

        $this->updateOroConfigValues($oroConfig, $configuration);

        return $oroConfig;
    }

    /**
     * Updates OroConfigValues with the values of our configuration model.
     * If no OroConfigValues exists for a value, a new one will be created and
     * added to the OroConfig entity.
     *
     * @param Config        $oroConfig
     * @param Configuration $configuration
     */
    private function updateOroConfigValues(Config $oroConfig, Configuration $configuration): void
    {
        $values = $configuration->getValues();

        foreach ($values as $key => $value) {
            $oroConfigValueAreadyExists = false;

            foreach ($oroConfig->getValues() as $oroConfigValue) {
                if ($key === $oroConfigValue->getName()) {
                    $oroConfigValue->setValue($value);

                    $oroConfigValueAreadyExists = true;
                    break;
                }
            }

            if (false === $oroConfigValueAreadyExists) {
                $oroConfigValue = new ConfigValue();
                $oroConfigValue->setConfig($oroConfig);
                $oroConfigValue->setName($key);
                $oroConfigValue->setValue($value);

                $oroConfig->getValues()->add($oroConfigValue);
            }
        }
    }
}
