<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Validation;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PimEnterprise\Component\ProductAsset\Model\Asset;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 *
 * TODO: should be acceptante tests.
 */
class AssetValidationIntegration extends TestCase
{
    public function testAssetUniqueEntity()
    {
        $asset = new Asset();
        $this->getUpdater()->update($asset, ['code' => 'cat']);

        $violations = $this->getValidator()->validate($asset);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame('This value is already used.', $violation->getMessage());
        $this->assertSame('code', $violation->getPropertyPath());
    }

    public function testAssetImmutableCode()
    {
        $asset = $this->get('pimee_api.repository.asset')->findOneByIdentifier('cat');
        $this->getUpdater()->update($asset, ['code' => 'new_code']);

        $violations = $this->getValidator()->validate($asset);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame('This property cannot be changed.', $violation->getMessage());
        $this->assertSame('code', $violation->getPropertyPath());
    }

    public function testAssetCodeNotBlank()
    {
        $asset = new Asset();
        $this->getUpdater()->update($asset, []);

        $violations = $this->getValidator()->validate($asset);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame('This value should not be blank.', $violation->getMessage());
        $this->assertSame('code', $violation->getPropertyPath());
    }

    public function testAssetCodeRegex()
    {
        $asset = new Asset();
        $this->getUpdater()->update($asset, ['code' => 'asset-code']);

        $violations = $this->getValidator()->validate($asset);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame('Asset code may contain only letters, numbers and underscores', $violation->getMessage());
        $this->assertSame('code', $violation->getPropertyPath());
    }

    public function testAssetCodeLength()
    {
        $asset = new Asset();
        $this->getUpdater()->update($asset, ['code' => str_pad('longCode', 101, "l")]);

        $violations = $this->getValidator()->validate($asset);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame('This value is too long. It should have 100 characters or less.', $violation->getMessage());
        $this->assertSame('code', $violation->getPropertyPath());
    }

    public function testAssetDescriptionLength()
    {
        $asset = new Asset();
        $this->getUpdater()->update($asset, [
            'code' => 'my_code',
            'description' => str_pad('a', 1000, "b")]
        );

        $violations = $this->getValidator()->validate($asset);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame('This value is too long. It should have 500 characters or less.', $violation->getMessage());
        $this->assertSame('description', $violation->getPropertyPath());
    }

    public function testAssetImmutableLocalizableProperty()
    {
        $asset = $this->get('pimee_api.repository.asset')->findOneByIdentifier('cat');
        $references = $asset->getReferences();
        count($references);
        $this->getUpdater()->update($asset, ['localized' => false]);

        $references = $asset->getReferences();
        count($references);

        $violations = $this->getValidator()->validate($asset);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame('This property cannot be changed.', $violation->getMessage());
        $this->assertSame('localized', $violation->getPropertyPath());
    }

    /**
     * @return ValidatorInterface
     */
    private function getValidator(): ValidatorInterface
    {
        return $this->get('validator');
    }

    /**
     * @return ObjectUpdaterInterface
     */
    private function getUpdater(): ObjectUpdaterInterface
    {
        return $this->get('pimee_product_asset.updater.asset');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
