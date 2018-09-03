<?php

namespace Pim\Behat\Context\Domain\Enrich;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\ProductSaver;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Tool\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Behat\Gherkin\Node\TableNode;
use Context\Spin\SpinCapableTrait;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Assert;
use Pim\Behat\Context\PimContext;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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

    /** @var VersionRepositoryInterface */
    private $versionRepository;

    /**
     * @param string                          $mainContextClass
     * @param ProductModelRepositoryInterface $productModelRepository
     * @param EntityManagerInterface          $entityManager
     * @param ObjectUpdaterInterface          $productModelUpdater
     * @param SaverInterface                  $productSaver
     * @param ValidatorInterface              $validator
     * @param VersionRepositoryInterface      $versionRepository
     */
    public function __construct(
        string $mainContextClass,
        ProductModelRepositoryInterface $productModelRepository,
        EntityManagerInterface $entityManager,
        ObjectUpdaterInterface $productModelUpdater,
        SaverInterface $productSaver,
        ValidatorInterface $validator,
        VersionRepositoryInterface $versionRepository
    ) {
        parent::__construct($mainContextClass);

        $this->entityManager = $entityManager;
        $this->productModelRepository = $productModelRepository;
        $this->productModelUpdater = $productModelUpdater;
        $this->productSaver = $productSaver;
        $this->validator = $validator;
        $this->versionRepository = $versionRepository;
    }

    /**
     * @param string $identifier
     *
     * @throws \Context\Spin\TimeoutException
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
     * @param string $productModelCode
     * @param string $rootProductModelCode
     *
     * @throws \Context\Spin\TimeoutException
     *
     * @When the parent of product model :productModelCode is changed for root product model :rootProductModelCode
     */
    public function changeProductModelParent(string $productModelCode, string $rootProductModelCode)
    {
        $productModel = $this->getProductModel($productModelCode);

        $this->productModelUpdater->update($productModel, ['parent' => $rootProductModelCode]);
        $this->validateProduct($productModel);
        $this->productSaver->save($productModel);
    }

    /**
     * @param string $productModelCode
     * @param string $rootProductModelCode
     *
     * @throws \Exception
     * @throws \Context\Spin\TimeoutException
     *
     * @return bool
     *
     * @Then the parent of product model :productModelCode cannot be changed for invalid root product model :rootProductModelCode
     */
    public function cannotSetInvalidProductModelParent(string $productModelCode, string $rootProductModelCode): bool
    {
        $productModel = $this->getProductModel($productModelCode);
        try {
            $this->productModelUpdater->update($productModel, ['parent' => $rootProductModelCode]);
            $this->validateProduct($productModel);
        } catch (InvalidPropertyException $e) {
            //The updater sends an exception because of the invalid root product model
            return true;
        } catch (\InvalidArgumentException $e) {
            //The validator sends an exception because of the invalid root product model
            return true;
        }

        throw new \Exception('An error should have happened during product model parent update');
    }

    /**
     * @param string $productModelCode
     * @param string $rootProductModelCode
     *
     * @throws \Context\Spin\TimeoutException
     *
     * @Then the parent of the product model :productModelCode should be :rootProductModelCode
     */
    public function productModelHasParent(string $productModelCode, string $rootProductModelCode)
    {
        $this->entityManager->clear();
        $entity = $this->getProductModel($productModelCode);
        Assert::assertSame($rootProductModelCode, $entity->getParent()->getCode());
    }

    /**
     * @param string    $code
     * @param TableNode $expectedVersion
     *
     * @throws \Context\Spin\TimeoutException
     *
     * @Then the last version of the product model :code should be:
     */
    public function checkProductModelLastVersion(string $code, TableNode $expectedVersion): void
    {
        $productModel = $this->getProductModel($code);
        $lastVersion = $this->versionRepository->getNewestLogEntry(
            ClassUtils::getClass($productModel),
            $productModel->getId()
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

        Assert::assertSame($expectedChangeset, $actualChangeset);
    }

    /**
     * @param string $code
     *
     * @throws \Context\Spin\TimeoutException
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
