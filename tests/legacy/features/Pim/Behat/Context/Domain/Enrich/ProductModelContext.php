<?php

namespace Pim\Behat\Context\Domain\Enrich;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Context\Spin\SpinCapableTrait;
use Doctrine\ORM\EntityManagerInterface;
use Pim\Behat\Context\PimContext;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductSaver;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductModelContext extends PimContext
{
    use SpinCapableTrait;

    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var ObjectUpdaterInterface */
    private $productModelUpdater;

    /** @var ProductSaver */
    private $productSaver;

    /** @var ValidatorInterface */
    private $validator;

    /**
     * @param string                          $mainContextClass
     * @param ProductModelRepositoryInterface $productModelRepository
     * @param EntityManagerInterface          $entityManager
     */
    public function __construct(
        string $mainContextClass,
        ProductModelRepositoryInterface $productModelRepository,
        EntityManagerInterface $entityManager,
        ObjectUpdaterInterface $productModelUpdater,
        SaverInterface $productSaver,
        ValidatorInterface $validator
    ) {
        parent::__construct($mainContextClass);

        $this->entityManager = $entityManager;
        $this->productModelRepository = $productModelRepository;
        $this->productModelUpdater = $productModelUpdater;
        $this->productSaver = $productSaver;
        $this->validator = $validator;
    }

    /**
     * @param string $identifier
     *
     * @Given /^I am on the "([^"]*)" product model page$/
     * @Given /^I edit the "([^"]*)" product model$/
     */
    public function iAmOnTheProductModelEditPage($identifier)
    {
        $page   = 'ProductModel';
        $entity = $this->getProductModel($identifier);
        $this->getNavigationContext()->openPage(sprintf('%s edit', $page), ['id' => $entity->getId()]);
    }

    /**
     * @When the parent of variant product :productModelCode is changed for :rootProductModelCode root product model
     */
    public function changeProductModelParent(string $productModelCode, string $rootProductModelCode)
    {
        $entity = $this->getProductModel($productModelCode);

        $this->productModelUpdater->update($entity, ['parent' => $rootProductModelCode]);
        $this->validateProduct($entity);
        $this->productSaver->save($entity);
    }

    /**
     * @Then the parent of the product model :productModelCode should be :rootProductModelCode
     */
    public function productModelHasParent(string $productModelCode, string $rootProductModelCode)
    {
        $this->entityManager->clear();
        $entity = $this->getProductModel($productModelCode);
        Assert::assertEquals($rootProductModelCode, $entity->getParent()->getCode());
    }

    /**
     * @param string $code
     *
     * @throws \InvalidArgumentException
     *
     * @return ProductModelInterface
     */
    private function getProductModel(string $code): ProductModelInterface
    {
        $productModel = $this->spin(function () use ($code) {
            return $this->productModelRepository->findOneByIdentifier($code);
        }, sprintf('Could not find a product model with code "%s"', $code));

        return $productModel;
    }

    /**
     * @param ProductModelInterface $productModel
     */
    private function validateProduct(ProductModelInterface $productModel)
    {
        $violations = $this->validator->validate($productModel);

        if (0 < $violations->count()) {
            $messages = [];
            foreach ($violations as $violation) {
                $messages[] = $violation->getMessage();
            }

            throw new \InvalidArgumentException(
                sprintf(
                    'Product model "%s" is not valid, cf following constraint violations "%s"',
                    $productModel->getCode(),
                    implode(', ', $messages)
                )
            );
        }
    }
}
