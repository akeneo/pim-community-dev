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

namespace PimEnterprise\Bundle\SuggestDataBundle\Doctrine\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\ConfigBundle\Entity\Config;
use Oro\Bundle\ConfigBundle\Entity\ConfigValue;
use PimEnterprise\Component\SuggestData\Model\Configuration;
use PimEnterprise\Component\SuggestData\Repository\ConfigurationRepositoryInterface;

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
    public function find(string $code): ?Configuration
    {
        $oroConfig = $this->getOroConfigRepository()->findOneBy([
            'scopedEntity' => $code,
            'recordId' => static::ORO_CONFIG_RECORD_ID,
        ]);

        if (null === $oroConfig) {
            return null;
        }

        $configurationFields = [];
        foreach ($oroConfig->getValues() as $oroConfigValue) {
            $configurationFields[$oroConfigValue->getName()] = $oroConfigValue->getValue();
        }

        return new Configuration($oroConfig->getEntity(), $configurationFields);
    }

    /**
     * {@inheritdoc}
     */
    public function save(Configuration $configuration): void
    {
        $oroConfig = $this->findOrCreateOroConfig($configuration->getCode());
        $this->createAndAddOroConfigValues($configuration->getConfigurationFields(), $oroConfig);

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
     * Retrieves an oro config entity from database if it exists, or creates a new one.
     *
     * @param string $code
     *
     * @return Config
     */
    private function findOrCreateOroConfig(string $code): Config
    {

        $oroConfig = $this->getOroConfigRepository()->findOneBy([
            'scopedEntity' => $code,
            'recordId' => static::ORO_CONFIG_RECORD_ID,
        ]);

        if (null === $oroConfig) {
            $oroConfig = new Config();
            $oroConfig->setEntity($code);
            $oroConfig->setRecordId(static::ORO_CONFIG_RECORD_ID);
        }

        return $oroConfig;
    }

    /**
     * @param array  $configurationFields
     * @param Config $oroConfig
     */
    private function createAndAddOroConfigValues(array $configurationFields, Config $oroConfig): void
    {
        $oroConfigValues = new ArrayCollection();
        foreach ($configurationFields as $fieldKey => $fieldValue) {
            $oroConfigValue = new ConfigValue();
            $oroConfigValue->setConfig($oroConfig);
            $oroConfigValue->setName($fieldKey);
            $oroConfigValue->setValue($fieldValue);

            $oroConfigValues->add($oroConfigValue);
        }

        $oroConfig->setValues($oroConfigValues);
    }
}
