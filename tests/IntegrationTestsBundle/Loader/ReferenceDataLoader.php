<?php

declare(strict_types=1);

namespace Akeneo\Test\IntegrationTestsBundle\Loader;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Integration test loader for reference data
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataLoader
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var array */
    protected $bundles;

    /** @var string */
    protected $acmeBundleName;

    /** @var string */
    protected $fabricClassName;

    /** @var string */
    protected $colorClassName;

    /**
     * @param EntityManagerInterface $entityManager
     * @param array                  $bundles
     * @param string                 $acmeBundleName
     * @param string                 $fabricClassName
     * @param string                 $colorClassName
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        array $bundles,
        string $acmeBundleName,
        string $fabricClassName,
        string $colorClassName
    ) {
        $this->entityManager = $entityManager;
        $this->bundles = $bundles;
        $this->acmeBundleName = $acmeBundleName;
        $this->fabricClassName = $fabricClassName;
        $this->colorClassName = $colorClassName;
    }

    /**
     * Load the reference data.
     */
    public function load(): void
    {
        if (!isset($this->bundles[$this->acmeBundleName])) {
            return;
        }

        $query = $this->entityManager->createQuery(sprintf('SELECT COUNT(f) FROM %s f', $this->fabricClassName));
        if (0 === (int) $query->getSingleScalarResult()) {
            $stmt = $this->entityManager->getConnection()->prepare($this->getFabricsSql());
            $stmt->execute();
        }

        $query = $this->entityManager->createQuery(sprintf('SELECT COUNT(c) FROM %s c', $this->colorClassName));
        if (0 === (int) $query->getSingleScalarResult()) {
            $stmt = $this->entityManager->getConnection()->prepare($this->getColorSql());
            $stmt->execute();
        }
    }

    private function getFabricsSql(): string
    {
        $path = __DIR__ . '/../../../src/Acme/Bundle/AppBundle/Resources/fixtures/fabrics.sql';

        return file_get_contents(realpath($path));
    }

    private function getColorSql(): string
    {
        $path = __DIR__ . '/../../../src/Acme/Bundle/AppBundle/Resources/fixtures/colors.sql';

        return file_get_contents(realpath($path));
    }
}
