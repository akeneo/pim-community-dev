<?php

namespace PimEnterprise\Bundle\ConnectorBundle\tests\integration\Import\Product;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Test\Integration\TestCase;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 *
 * +----------+-------------------------------+-----------------------------------+-----------------------------------------------------+
 * |          |          Categories           |             Locales               |                  Attribute groups                   |
 * +  Roles   +-------------------------------+-----------------------------------+-----------------------------------+-----------------+
 * |          |   categoryA2  |   categoryB   |   en_US   |   fr_FR   |   de_DE   | attributeGroupA | attributeGroupB | attributeGroupC |
 * +----------+-------------------------------+-----------------------------------+-----------------------------------------------------+
 * | Redactor |      View     |     -         | View,Edit |    View   |     -     |    View,Edit    |      View       |        -        |
 * | Manager  | View,Edit,Own | View,Edit,Own | View,Edit | View,Edit | View,Edit |    View,Edit    |    View,Edit    |    View,Edit    |
 * +----------+-------------------------------+-----------------------------------+-----------------------------------------------------+
 *
 * Check that the completeness is well calculated on authenticated imports.
 * The completeness should be the same after the import, whoever the user importing the data, and not calculated according to the permissions of the user.
 *
 */
class CompletenessPerUserAfterImportIntegration extends TestCase
{
    /** @var JobLauncher */
    protected $jobLauncher;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->jobLauncher = new JobLauncher(static::$kernel);

        $this->createFamily([
            'code'                   => 'my_family',
            'attributes'             => ['sku', 'a_localized_and_scopable_text_area', 'a_number_float', 'a_localizable_image', 'a_metric_without_decimal_negative'],
            'attribute_requirements' => [
                'tablet'          => ['sku', 'a_localized_and_scopable_text_area', 'a_number_float', 'a_localizable_image', 'a_metric_without_decimal_negative'],
                'ecommerce'       => ['sku', 'a_localized_and_scopable_text_area'],
                'ecommerce_china' => ['sku', 'a_localized_and_scopable_text_area', 'a_number_float'],
            ],
        ]);
    }

    public function testCompletenessAfterImportWithPermissionsForTheRedactor()
    {

        $content = <<<CSV
sku;categories;enabled;family;groups;a_localized_and_scopable_text_area-en_US-tablet
product_viewable_by_everybody_1;categoryA2;1;my_family;;"EN tablet"
CSV;

        $this->jobLauncher->launchAuthenticatedImport('csv_product_import', $content, 'mary', [$this->getFixturePath('akeneo.pdf')]);
        $this->get('doctrine')->getManager()->clear();

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_viewable_by_everybody_1');

        $completenesses = $product->getCompletenesses()->toArray();

        $this->assertCompleteness('ecommerce', 'en_US', 50, $completenesses);
        $this->assertCompleteness('ecommerce_china', 'en_US', 33, $completenesses);
        $this->assertCompleteness('ecommerce_china', 'zh_CN', 33, $completenesses);
        $this->assertCompleteness('tablet', 'de_DE', 20, $completenesses);
        $this->assertCompleteness('tablet', 'en_US', 40, $completenesses);
        $this->assertCompleteness('tablet', 'fr_FR', 20, $completenesses);
    }

    public function testCompletenessAfterImportWithPermissionsForTheManager()
    {

        $content = <<<CSV
sku;categories;enabled;family;groups;a_localized_and_scopable_text_area-en_US-tablet
product_viewable_by_everybody_1;categoryA2;1;my_family;;"EN tablet"
CSV;

        $this->jobLauncher->launchAuthenticatedImport('csv_product_import', $content, 'julia');
        $this->get('doctrine')->getManager()->clear();

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_viewable_by_everybody_1');

        $completenesses = $product->getCompletenesses()->toArray();

        $this->assertCompleteness('ecommerce', 'en_US', 50, $completenesses);
        $this->assertCompleteness('ecommerce_china', 'en_US', 33, $completenesses);
        $this->assertCompleteness('ecommerce_china', 'zh_CN', 33, $completenesses);
        $this->assertCompleteness('tablet', 'de_DE', 20, $completenesses);
        $this->assertCompleteness('tablet', 'en_US', 40, $completenesses);
        $this->assertCompleteness('tablet', 'fr_FR', 20, $completenesses);
    }

    public function testCompletenessPermissionsAfterImportOfExistingProductsForTheRedactor()
    {

        $content = <<<CSV
sku;categories;enabled;family;groups;a_localizable_image-en_US;a_localizable_image-fr_FR;a_localized_and_scopable_text_area-fr_FR-tablet;a_number_float
product_viewable_by_everybody_1;master;1;my_family;;fixtures/akeneo.pdf;fixtures/akeneo.pdf;"EN tablet";12.0500
CSV;

        $this->jobLauncher->launchAuthenticatedImport('csv_product_import', $content, 'julia', [$this->getFixturePath('akeneo.pdf')]);

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_viewable_by_everybody_1');

        $completenesses = $product->getCompletenesses()->toArray();

        $this->assertCompleteness('ecommerce', 'en_US', 50, $completenesses);
        $this->assertCompleteness('ecommerce_china', 'en_US', 66, $completenesses);
        $this->assertCompleteness('ecommerce_china', 'zh_CN', 66, $completenesses);
        $this->assertCompleteness('tablet', 'de_DE', 40, $completenesses);
        $this->assertCompleteness('tablet', 'en_US', 60, $completenesses);
        $this->assertCompleteness('tablet', 'fr_FR', 80, $completenesses);

        $content = <<<CSV
sku;categories;enabled;family;groups;a_localized_and_scopable_text_area-en_US-tablet
product_viewable_by_everybody_1;master;1;my_family;;"EN tablet"
CSV;

        $this->jobLauncher->launchAuthenticatedImport('csv_product_import', $content, 'mary');
        $this->get('doctrine')->getManager()->clear();

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_viewable_by_everybody_1');

        $completenesses = $product->getCompletenesses()->toArray();

        $this->assertCompleteness('ecommerce', 'en_US', 50, $completenesses);
        $this->assertCompleteness('ecommerce_china', 'en_US', 66, $completenesses);
        $this->assertCompleteness('ecommerce_china', 'zh_CN', 66, $completenesses);
        $this->assertCompleteness('tablet', 'de_DE', 40, $completenesses);
        $this->assertCompleteness('tablet', 'en_US', 80, $completenesses);
        $this->assertCompleteness('tablet', 'fr_FR', 80, $completenesses);
    }

    /**
     * @param string $identifier
     * @param array  $data
     *
     * @return ProductInterface
     */
    protected function createProduct(string $identifier, array $data = []) : ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->get('akeneo_elasticsearch.client.product')->refreshIndex();

        return $product;
    }

    /**
     * @param array $data
     *
     * @return FamilyInterface
     */
    protected function createFamily(array $data = []) : FamilyInterface
    {
        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update($family, $data);
        $this->get('pim_catalog.saver.family')->save($family);

        return $family;
    }

    /**
     * @param string $channel
     * @param string $locale
     * @param int    $ratio
     * @param array  $completenesses
     *
     * @throws \LogicException
     */
    protected function assertCompleteness(string $channel, string $locale, int $ratio, array $completenesses) : void
    {
        foreach ($completenesses as $completeness) {
            if ($channel === $completeness->getChannel()->getCode() && $locale === $completeness->getLocale()->getCode()) {
                $this->assertSame(
                    $completeness->getRatio(),
                    $ratio,
                    sprintf('Wrong completeness for channel "%s" and locale "%s"', $channel, $locale)
                );

                return;
            }
        }

        throw new \LogicException(
            sprintf('Completeness for the channel "%s" and locale "%s" does not exist.', $channel, $locale)
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        $rootPath = $this->getParameter('kernel.root_dir') . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;

        return new Configuration(
            [
                Configuration::getTechnicalCatalogPath(),
                $rootPath . 'tests' . DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'technical'
            ]
        );
    }
}
