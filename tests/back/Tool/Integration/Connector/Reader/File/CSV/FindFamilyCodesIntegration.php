<?php

declare(strict_types=1);

namespace AkeneoTest\Tool\Integration\Connector\Reader\File;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\File\FindFamilyCodesInterface;
use Akeneo\Test\Integration\TestCase;
/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindFamilyCodesIntegration extends TestCase
{
    private const CSV_FILE_PATH = __DIR__ . DIRECTORY_SEPARATOR . 'families.csv';

    public FindFamilyCodesInterface $findFamilyCodes;

    public function setUp(): void
    {
        parent::setUp();
        $this->findFamilyCodes = $this->get('pim_connector.reader.file.csv.find_family_codes');
    }

    /** @test */
    public function it_returns_the_list_of_asset_family_codes(): void
    {
        $familyCodes = $this->findFamilyCodes->execute(self::CSV_FILE_PATH);

        self::assertSame(['family1', 'family2'], iterator_to_array($familyCodes));
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
