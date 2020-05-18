<?php
declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Aspell;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Dictionary;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LanguageCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Aspell\AspellDictionary;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

final class AspellDictionaryIntegration extends TestCase
{
    public function test_it_generate_a_dictionary_on_filesystem()
    {
        $dictionary = new Dictionary(['silence', 'is', 'gold']);

        $this->get(AspellDictionary::class)
            ->persistDictionaryToSharedFilesystem(
                $dictionary,
                new LanguageCode('en')
            );

        $fs = $this->get('oneup_flysystem.mount_manager')
            ->getFilesystem('dataQualityInsightsSharedAdapter');

        $this->assertTrue($fs->has('consistency/text_checker/aspell/custom-dictionary-en.pws'));

        $expected = <<<DICTIONARY
personal_ws-1.1 en 3
silence
is
gold

DICTIONARY;

        $actual = $fs->read('consistency/text_checker/aspell/custom-dictionary-en.pws');

        $this->assertSame($expected, $actual);
    }

    protected function tearDown(): void
    {
        $this->ensureDictionariesAreRemoved();

        parent::tearDown();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureDictionariesAreRemoved();
    }

    private function ensureDictionariesAreRemoved()
    {
        $fs = $this->get('oneup_flysystem.mount_manager')
            ->getFilesystem('dataQualityInsightsSharedAdapter');

        $files = $fs->listContents('consistency/text_checker/aspell');

        foreach ($files as $file) {
            $fs->delete($file['path']);
        }
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
