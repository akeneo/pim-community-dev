<?php

declare(strict_types=1);

namespace PimEnterprise\Bundle\ProductAssetBundle\tests\EndToEnd\Command;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GenerateMissingVariationFilesCommandIntegration extends TestCase
{
    /**
     * @test
     */
    public function it_generates_the_variations_for_one_asset()
    {
    }

    /**
     * @test
     */
    public function it_generates_the_variations_for_all_assets()
    {
    }

    /**
     * @test
     */
    public function when_a_new_channel_is_activated_it_generates_the_missing_variations_files_for_this_asset()
    {
    }

    /**
     * @test
     */
    public function when_a_locale_is_activated_it_generates_the_missing_variations_files_for_this_asset()
    {
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
