<?php

declare(strict_types=1);

namespace Pim\Behat\Context\Domain\Enrich;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @author    Julian Prud'homme <julian.prudhomme@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductContext implements Context
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $productRepository;

    /** @var ObjectUpdaterInterface */
    private $productUpdater;

    /** @var SaverInterface|BulkSaverInterface */
    private $productSaver;

    /** @var ValidatorInterface */
    private $validator;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var VersionRepositoryInterface */
    private $versionRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $productRepository
     * @param ObjectUpdaterInterface                $productUpdater
     * @param SaverInterface                        $productSaver
     * @param ValidatorInterface                    $validator
     * @param EntityManagerInterface                $entityManager
     * @param VersionRepositoryInterface            $versionRepository
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $productRepository,
        ObjectUpdaterInterface $productUpdater,
        SaverInterface $productSaver,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager,
        VersionRepositoryInterface $versionRepository
    ) {
        $this->productRepository = $productRepository;
        $this->productUpdater = $productUpdater;
        $this->productSaver = $productSaver;
        $this->validator = $validator;
        $this->entityManager = $entityManager;
        $this->versionRepository = $versionRepository;
    }

    /**
     * @param string $productIdentifier
     * @param string $productModelCode
     *
     * @When the parent of variant product :productIdentifier is changed for :productModelCode product model
     */
    public function changeVariantProductParent(string $productIdentifier, string $productModelCode): void
    {
        $product = $this->findProduct($productIdentifier);

        $this->productUpdater->update($product, ['parent' => $productModelCode]);
        $this->validateProduct($product);
        $this->productSaver->save($product);
    }

    /**
     * @param TableNode $table
     *
     * @When the parents of the following products are changed:
     */
    public function changeManyVariantProductsParents(TableNode $table): void
    {
        $products = [];
        foreach ($table->getHash() as $data) {
            $product = $this->findProduct($data['sku']);
            $this->productUpdater->update($product, ['parent' => $data['parent']]);
            $this->validateProduct($product);
            $products[] = $product;
        }

        $this->productSaver->saveAll($products);
    }

    /**
     * @param string $productIdentifier
     * @param string $productModelCode
     *
     * @When the parent of variant product :productIdentifier is changed for incorrect :productModelCode product model
     */
    public function setInvalidParent(string $productIdentifier, string $productModelCode): void
    {
        $product = $this->findProduct($productIdentifier);

        $this->productUpdater->update($product, ['parent' => $productModelCode]);
    }

    /**
     * @param string    $identifier
     * @param TableNode $expectedVersion
     *
     * @Then the last version of the product :identifier should be:
     * @Then the last version of the variant product :identifier should be:
     */
    public function checkProductLastVersion(string $identifier, TableNode $expectedVersion): void
    {
        $product = $this->findProduct($identifier);
        $lastVersion = $this->versionRepository->getNewestLogEntry(
            ClassUtils::getClass($product),
            $product->getId()
        );
        $actualChangeset = $lastVersion->getChangeset();

        $expectedChangeset = [];
        foreach ($expectedVersion->getHash() as $expectedVersionField) {
            $expectedChangeset[$expectedVersionField['field']] = [
                'old' => $expectedVersionField['old_value'],
                'new' => $expectedVersionField['new_value'],
            ];
        }

        ksort($actualChangeset);
        ksort($expectedChangeset);

        Assert::same($actualChangeset, $expectedChangeset);
    }

    /**
     * @param string $identifier
     *
     * @throws \InvalidArgumentException
     *
     * @return ProductInterface
     */
    private function findProduct(string $identifier): ProductInterface
    {
        $product = $this->productRepository->findOneByIdentifier($identifier);
        if (null === $product) {
            throw new \InvalidArgumentException(sprintf('The product "%s" does not exist.', $identifier));
        }

        return $product;
    }

    /**
     * @param ProductInterface $product
     */
    private function validateProduct(ProductInterface $product)
    {
        $violations = $this->validator->validate($product);

        if (0 !== $violations->count()) {
            $messages = [];
            foreach ($violations as $violation) {
                $messages[] = $violation->getMessage();
            }

            throw new \InvalidArgumentException(
                sprintf(
                    'Product "%s" is not valid, cf following constraint violations "%s"',
                    $product->getIdentifier(),
                    implode(', ', $messages)
                )
            );
        }
    }
}
