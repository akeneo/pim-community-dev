<?php

namespace AkeneoTest\Tool\Integration\Connector\Writer\File;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\Csv\ProductWriter;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Writer\File\WrittenFileInfo;
use Symfony\Component\Filesystem\Filesystem;

class CsvWriterIntegration extends TestCase
{
    private const EXAMPLES_DIRECTORY = 'examples';

    protected function setUp(): void
    {
        parent::setUp();

        $this->removeOutputDirectory();
    }

    public function provideFlushExamples(): iterable
    {
        yield 'Into a file' => [
            'items' => [
                [
                    'sku' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'my_product',
                        ]
                    ],
                    'name' => [
                        [
                            'scope' => null,
                            'locale' => 'en_US',
                            'data' => 'My product',
                        ]
                    ],
                    'description' => [
                        [
                            'scope' => 'mobile',
                            'locale' => 'en_US',
                            'data' => 'A nice product',
                        ]
                    ],
                    'color' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'blue',
                        ],
                    ],
                    'rate_sale' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 10,
                        ],
                    ],
                ],
                [
                    'sku' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'my_second_product',
                        ]
                    ],
                    'name' => [
                        [
                            'scope' => null,
                            'locale' => 'en_US',
                            'data' => 'My second product',
                        ],
                        [
                            'scope' => null,
                            'locale' => 'fr_FR',
                            'data' => 'Mon second produit',
                        ],
                    ],
                    'description' => [
                        [
                            'scope' => 'mobile',
                            'locale' => 'fr_FR',
                            'data' => 'Le deuxième de la collection',
                        ],
                    ],
                    'color' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'white',
                        ],
                    ],
                ],
            ],
            'attributes' => [],
            'simple_export.csv',
            -1,
            [
                'simple_export.csv',
            ],
            [
                'simple.csv',
            ],
        ];
        yield 'Into a file with additional headers' => [
            [
                [
                    'sku' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'my_product',
                        ],
                    ],
                    'name' => [
                        [
                            'scope' => null,
                            'locale' => 'en_US',
                            'data' => 'My product',
                        ],
                    ],
                    'description' => [
                        [
                            'scope' => 'mobile',
                            'locale' => 'en_US',
                            'data' => 'A nice product',
                        ],
                    ],
                    'color' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'blue',
                        ],
                    ],
                    'rate_sale' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 10,
                        ],
                    ],
                ],
                [
                    'sku' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'my_second_product',
                        ],
                    ],
                    'name' => [
                        [
                            'scope' => null,
                            'locale' => 'en_US',
                            'data' => 'My second product',
                        ],
                        [
                            'scope' => null,
                            'locale' => 'fr_FR',
                            'data' => 'Mon second produit',
                        ],
                    ],
                    'description' => [
                        [
                            'scope' => 'mobile',
                            'locale' => 'fr_FR',
                            'data' => 'Le deuxième de la collection',
                        ],
                    ],
                    'color' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'white',
                        ],
                    ],
                ],
            ],
            [
                'sku',
                'color',
                'description',
                'name',
                'rate_sale',
                'size',
                'weight',
            ],
            'additional_headers_export.csv',
            -1,
            [
                'additional_headers_export.csv',
            ],
            [
                'additional_headers.csv',
            ],
        ];
        yield 'Into a file with no header' => [
            [
                [
                    'sku' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'my_product',
                        ],
                    ],
                    'name' => [
                        [
                            'scope' => null,
                            'locale' => 'en_US',
                            'data' => 'My product',
                        ],
                    ],
                    'description' => [
                        [
                            'scope' => 'mobile',
                            'locale' => 'en_US',
                            'data' => 'A nice product',
                        ],
                    ],
                    'color' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'blue',
                        ],
                    ],
                    'rate_sale' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 10,
                        ],
                    ],
                ],
                [
                    'sku' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'my_second_product',
                        ],
                    ],
                    'name' => [
                        [
                            'scope' => null,
                            'locale' => 'en_US',
                            'data' => 'My second product',
                        ],
                        [
                            'scope' => null,
                            'locale' => 'fr_FR',
                            'data' => 'Mon second produit',
                        ],
                    ],
                    'description' => [
                        [
                            'scope' => 'mobile',
                            'locale' => 'fr_FR',
                            'data' => 'Le deuxième de la collection',
                        ],
                    ],
                    'color' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'white',
                        ],
                    ],
                ],
            ],
            [],
            'no_header_export.csv',
            -1,
            [
                'no_header_export.csv',
            ],
            [
                'no_header.csv',
            ],
            false,
        ];
        yield 'Into a file translated in English' => [
            [
                [
                    'sku' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'my_product',
                        ],
                    ],
                    'name' => [
                        [
                            'scope' => null,
                            'locale' => 'en_US',
                            'data' => 'My product',
                        ],
                    ],
                    'description' => [
                        [
                            'scope' => 'mobile',
                            'locale' => 'en_US',
                            'data' => 'A nice product',
                        ],
                    ],
                    'color' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'blue',
                        ],
                    ],
                    'rate_sale' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 10,
                        ],
                    ],
                ],
                [
                    'sku' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'my_second_product',
                        ],
                    ],
                    'name' => [
                        [
                            'scope' => null,
                            'locale' => 'en_US',
                            'data' => 'My second product',
                        ],
                        [
                            'scope' => null,
                            'locale' => 'fr_FR',
                            'data' => 'Mon second produit',
                        ],
                    ],
                    'description' => [
                        [
                            'scope' => 'mobile',
                            'locale' => 'fr_FR',
                            'data' => 'Le deuxième de la collection',
                        ],
                    ],
                    'color' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'white',
                        ],
                    ],
                ],
            ],
            [],
            'translated_export.csv',
            -1,
            [
                'translated_export.csv',
            ],
            [
                'translated.csv',
            ],
            true,
            true,
            true,
        ];
        yield 'Into a file with additional headers translated in English' => [
            [
                [
                    'sku' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'my_product',
                        ],
                    ],
                    'name' => [
                        [
                            'scope' => null,
                            'locale' => 'en_US',
                            'data' => 'My product',
                        ],
                    ],
                    'description' => [
                        [
                            'scope' => 'mobile',
                            'locale' => 'en_US',
                            'data' => 'A nice product',
                        ],
                    ],
                    'color' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'blue',
                        ],
                    ],
                    'rate_sale' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 10,
                        ],
                    ],
                ],
                [
                    'sku' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'my_second_product',
                        ],
                    ],
                    'name' => [
                        [
                            'scope' => null,
                            'locale' => 'en_US',
                            'data' => 'My second product',
                        ],
                        [
                            'scope' => null,
                            'locale' => 'fr_FR',
                            'data' => 'Mon second produit',
                        ],
                    ],
                    'description' => [
                        [
                            'scope' => 'mobile',
                            'locale' => 'fr_FR',
                            'data' => 'Le deuxième de la collection',
                        ],
                    ],
                    'color' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'white',
                        ],
                    ],
                ],
            ],
            [
                'sku',
                'color',
                'description',
                'name',
                'rate_sale',
                'size',
                'weight',
            ],
            'additional_headers_translated_export.csv',
            -1,
            [
                'additional_headers_translated_export.csv',
            ],
            [
                'additional_headers_translated.csv',
            ],
            true,
            true,
            true,
        ];
        yield 'Into a file with additional headers translated in French' => [
            [
                [
                    'sku' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'my_product',
                        ],
                    ],
                    'name' => [
                        [
                            'scope' => null,
                            'locale' => 'en_US',
                            'data' => 'My product',
                        ],
                    ],
                    'description' => [
                        [
                            'scope' => 'mobile',
                            'locale' => 'en_US',
                            'data' => 'A nice product',
                        ],
                    ],
                    'color' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'blue',
                        ],
                    ],
                    'rate_sale' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 10,
                        ],
                    ],
                ],
                [
                    'sku' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'my_second_product',
                        ],
                    ],
                    'name' => [
                        [
                            'scope' => null,
                            'locale' => 'en_US',
                            'data' => 'My second product',
                        ],
                        [
                            'scope' => null,
                            'locale' => 'fr_FR',
                            'data' => 'Mon second produit',
                        ],
                    ],
                    'description' => [
                        [
                            'scope' => 'mobile',
                            'locale' => 'fr_FR',
                            'data' => 'Le deuxième de la collection',
                        ],
                    ],
                    'color' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'white',
                        ],
                    ],
                ],
            ],
            [
                'sku',
                'color',
                'description',
                'name',
                'rate_sale',
                'size',
                'weight',
            ],
            'additional_headers_translated_french_export.csv',
            -1,
            [
                'additional_headers_translated_french_export.csv',
            ],
            [
                'additional_headers_translated_french.csv',
            ],
            true,
            true,
            true,
            'fr_FR',
        ];
        yield 'Into a file with only values translated' => [
            [
                [
                    'sku' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'my_product',
                        ]
                    ],
                    'values' => [
                        'name' => [
                            [
                                'scope' => null,
                                'locale' => 'en_US',
                                'data' => 'My product',
                            ]
                        ],
                        'description' => [
                            [
                                'scope' => 'mobile',
                                'locale' => 'en_US',
                                'data' => 'A nice product',
                            ]
                        ],
                        'color' => [
                            [
                                'scope' => null,
                                'locale' => null,
                                'data' => 'blue',
                            ]
                        ],
                        'rate_sale' => [
                            [
                                'scope' => null,
                                'locale' => null,
                                'data' => 10,
                            ]
                        ],
                    ],
                ],
                [
                    'sku' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'my_second_product',
                        ]
                    ],
                    'values' => [
                        'name' => [
                            [
                                'scope' => null,
                                'locale' => 'en_US',
                                'data' => 'My second product',
                            ],
                            [
                                'scope' => null,
                                'locale' => 'fr_FR',
                                'data' => 'Mon second produit',
                            ],
                        ],
                        'description' => [
                            [
                                'scope' => 'mobile',
                                'locale' => 'fr_FR',
                                'data' => 'Le deuxième de la collection',
                            ]
                        ],
                        'color' => [
                            [
                                'scope' => null,
                                'locale' => null,
                                'data' => 'white',
                            ]
                        ],
                    ],
                ],
            ],
            [],
            'only_values_translated_export.csv',
            -1,
            [
                'only_values_translated_export.csv',
            ],
            [
                'only_values_translated.csv',
            ],
            true,
            true,
            false,
        ];
        yield 'Into a file translated with no header' => [
            [
                [
                    'sku' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'my_product',
                        ],
                    ],
                    'name' => [
                        [
                            'scope' => null,
                            'locale' => 'en_US',
                            'data' => 'My product',
                        ],
                    ],
                    'description' => [
                        [
                            'scope' => 'mobile',
                            'locale' => 'en_US',
                            'data' => 'A nice product',
                        ],
                    ],
                    'color' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'blue',
                        ],
                    ],
                    'rate_sale' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 10,
                        ],
                    ],
                ],
                [
                    'sku' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'my_second_product',
                        ],
                    ],
                    'name' => [
                        [
                            'scope' => null,
                            'locale' => 'en_US',
                            'data' => 'My second product',
                        ],
                        [
                            'scope' => null,
                            'locale' => 'fr_FR',
                            'data' => 'Mon second produit',
                        ],
                    ],
                    'description' => [
                        [
                            'scope' => 'mobile',
                            'locale' => 'fr_FR',
                            'data' => 'Le deuxième de la collection',
                        ],
                    ],
                    'color' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'white',
                        ],
                    ],
                ],
            ],
            [],
            'no_header_translated_export.csv',
            -1,
            [
                'no_header_translated_export.csv',
            ],
            [
                'no_header_translated.csv',
            ],
            false,
            true,
            false,
        ];
        yield 'Into multiple files with no extension' => [
            [
                [
                    'sku' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'my_product',
                        ],
                    ],
                    'name' => [
                        [
                            'scope' => null,
                            'locale' => 'en_US',
                            'data' => 'My product',
                        ],
                    ],
                    'description' => [
                        [
                            'scope' => 'mobile',
                            'locale' => 'en_US',
                            'data' => 'A nice product',
                        ],
                    ],
                    'color' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'blue',
                        ],
                    ],
                    'rate_sale' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 10,
                        ],
                    ],
                ],
                [
                    'sku' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'my_second_product',
                        ],
                    ],
                    'name' => [
                        [
                            'scope' => null,
                            'locale' => 'en_US',
                            'data' => 'My second product',
                        ],
                        [
                            'scope' => null,
                            'locale' => 'fr_FR',
                            'data' => 'Mon second produit',
                        ],
                    ],
                    'description' => [
                        [
                            'scope' => 'mobile',
                            'locale' => 'fr_FR',
                            'data' => 'Le deuxième de la collection',
                        ],
                    ],
                    'color' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'white',
                        ],
                    ],
                ],
                [
                    'sku' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'my_third_product',
                        ],
                    ],
                    'name' => [
                        [
                            'scope' => null,
                            'locale' => 'en_US',
                            'data' => 'My third product',
                        ],
                        [
                            'scope' => null,
                            'locale' => 'fr_FR',
                            'data' => 'Mon troisième produit',
                        ],
                    ],
                    'description' => [
                        [
                            'scope' => 'mobile',
                            'locale' => 'en_US',
                            'data' => 'The last one',
                        ],
                    ],
                ],
            ],
            [],
            'no_extension_export',
            2,
            [
                'no_extension_export_1',
                'no_extension_export_2',
            ],
            [
                'no_extension_1.csv',
                'no_extension_2.csv',
            ],
            true,
            true,
            true,
        ];
        yield 'Into multiple files with additional headers translated in English' => [
            [
                [
                    'sku' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'my_product',
                        ],
                    ],
                    'name' => [
                        [
                            'scope' => null,
                            'locale' => 'en_US',
                            'data' => 'My product',
                        ],
                    ],
                    'description' => [
                        [
                            'scope' => 'mobile',
                            'locale' => 'en_US',
                            'data' => 'A nice product',
                        ],
                    ],
                    'color' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'blue',
                        ],
                    ],
                    'rate_sale' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 10,
                        ],
                    ],
                ],
                [
                    'sku' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'my_second_product',
                        ],
                    ],
                    'name' => [
                        [
                            'scope' => null,
                            'locale' => 'en_US',
                            'data' => 'My second product',
                        ],
                        [
                            'scope' => null,
                            'locale' => 'fr_FR',
                            'data' => 'Mon second produit',
                        ],
                    ],
                    'description' => [
                        [
                            'scope' => 'mobile',
                            'locale' => 'fr_FR',
                            'data' => 'Le deuxième de la collection',
                        ],
                    ],
                    'color' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'white',
                        ],
                    ],
                ],
                [
                    'sku' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'my_third_product',
                        ],
                    ],
                    'name' => [
                        [
                            'scope' => null,
                            'locale' => 'en_US',
                            'data' => 'My third product',
                        ],
                        [
                            'scope' => null,
                            'locale' => 'fr_FR',
                            'data' => 'Mon troisième produit',
                        ],
                    ],
                    'description' => [
                        [
                            'scope' => 'mobile',
                            'locale' => 'en_US',
                            'data' => 'The last one',
                        ],
                    ],
                    'color' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'red',
                        ],
                    ],
                ],
            ],
            [
                'sku',
                'color',
                'description',
                'name',
                'rate_sale',
                'size',
                'weight',
            ],
            'additional_headers_translated_export.csv',
            2,
            [
                'additional_headers_translated_export_1.csv',
                'additional_headers_translated_export_2.csv',
            ],
            [
                'additional_headers_translated_1.csv',
                'additional_headers_translated_2.csv',
            ],
            true,
            true,
            true,
        ];
        yield 'Nothing if the buffer is empty' => [
            [],
            [],
            'empty.csv',
            -1,
            [],
            [],
            false,
        ];
    }

    /**
     * @dataProvider provideFlushExamples
     */
    public function test_it_flushes(
        array  $flatItems,
        array  $additionalHeaders,
        string $fileName,
        int    $maxLinesPerFile,
        array  $expectedWrittenFiles,
        array  $examplesFiles,
        bool   $withHeader = true,
        bool   $valuesShouldBeTranslated = false,
        bool   $headersShouldBeTranslated = false,
        string $locale = 'en_US',
    ): void
    {
        $stepExecution = $this->createStepExecution($fileName, $withHeader, $additionalHeaders, $maxLinesPerFile, $valuesShouldBeTranslated, $headersShouldBeTranslated, $locale);
        $this->getWriter()->setStepExecution($stepExecution);
        $this->getWriter()->initialize();
        $this->getWriter()->write($flatItems);
        $this->getWriter()->flush();

        $actualWrittenFiles = array_map(
            fn (WrittenFileInfo $writtenFileInfo) => $writtenFileInfo->sourceKey(),
            $this->getWriter()->getWrittenFiles()
        );

        $expectedWrittenFiles = array_map(
            fn (string $fileName) => $this->getOutputFilePath($fileName),
            $expectedWrittenFiles
        );

        $this->assertSame($expectedWrittenFiles, $actualWrittenFiles);

        if ([] === $expectedWrittenFiles) {
            $this->assertFileDoesNotExist($this->getOutputFilePath($fileName));
            return;
        }

        foreach ($actualWrittenFiles as $index => $actualWrittenFile) {
            $this->assertFileExists($actualWrittenFile);
            $this->assertFileEquals($this->getExamplesFilePath($examplesFiles[$index]), $actualWrittenFile);
        }
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('footwear');
    }

    private function createStepExecution(
        string $filePath,
        bool   $withHeader,
        array  $additionalHeaders,
        int $maxLinesPerFile,
        bool   $valuesShouldBeTranslated = false,
        bool   $headersShouldBeTranslated = false,
        string $locale = 'en_US',
    ): StepExecution {
        $rawParameters = [
            'withHeader' => $withHeader,
            'with_label' => $valuesShouldBeTranslated,
            'header_with_label' => $headersShouldBeTranslated,
            'file_locale' => $locale,
            'scope' => 'mobile',
            'delimiter' => ',',
            'enclosure' => '"',
            'filters' => [
                'structure' => [
                    'locales' => ['en_US', 'fr_FR'],
                    'attributes' => $additionalHeaders,
                ]
            ],
            'storage' => [
                'type' => 'local',
                'file_path' => $this->getOutputFilePath($filePath),
            ],
            'linesPerFile' => $maxLinesPerFile,
        ];
        $jobExecution = new JobExecution();
        $jobExecution->setJobParameters(new JobParameters($rawParameters));

        return new StepExecution('export', $jobExecution);
    }

    private function removeOutputDirectory(): void
    {
        $outputDirectoryPath = sprintf('%s/var/tests/output', static::$kernel->getProjectDir());
        if (!is_dir($outputDirectoryPath)) {
            return;
        }

        $filesytem = new Filesystem();
        $filesytem->remove($outputDirectoryPath);
    }

    private function getExamplesFilePath(string $exampleFileName): string
    {
        return sprintf('%s/%s/%s', __DIR__, self::EXAMPLES_DIRECTORY, $exampleFileName);
    }

    private function getOutputFilePath(string $fileName): string
    {
        return sprintf('%s/var/tests/output/%s', static::$kernel->getProjectDir(), $fileName);
    }

    private function getWriter(): ProductWriter
    {
        return $this->get('pim_connector.writer.file.csv_product');
    }
}
