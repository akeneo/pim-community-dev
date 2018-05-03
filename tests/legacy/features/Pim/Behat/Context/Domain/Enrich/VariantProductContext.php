<?php

declare(strict_types=1);

namespace Pim\Behat\Context\Domain\Enrich;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Behat\Gherkin\Node\TableNode;
use Context\Spin\SpinCapableTrait;
use Doctrine\ORM\EntityManagerInterface;
use Pim\Behat\Context\PimContext;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @author    Julian Prud'homme <julian.prudhomme@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantProductContext extends PimContext
{
    use SpinCapableTrait;

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

    /**
     * @param string                                $mainContextClass
     * @param IdentifiableObjectRepositoryInterface $productRepository
     * @param ObjectUpdaterInterface                $productUpdater
     * @param SaverInterface                        $productSaver
     * @param ValidatorInterface                    $validator
     * @param EntityManagerInterface                $entityManager
     */
    public function __construct(
        string $mainContextClass,
        IdentifiableObjectRepositoryInterface $productRepository,
        ObjectUpdaterInterface $productUpdater,
        SaverInterface $productSaver,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct($mainContextClass);

        $this->productRepository = $productRepository;
        $this->productUpdater = $productUpdater;
        $this->productSaver = $productSaver;
        $this->validator = $validator;
        $this->entityManager = $entityManager;
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
