<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Syndication\Test\Acceptance\UseCases\Property;

use Akeneo\Platform\Syndication\Application\Common\Column\Column;
use Akeneo\Platform\Syndication\Application\Common\Column\ColumnCollection;
use Akeneo\Platform\Syndication\Application\Common\Format\ConcatFormat;
use Akeneo\Platform\Syndication\Application\Common\Format\ElementCollection;
use Akeneo\Platform\Syndication\Application\Common\Format\SourceElement;
use Akeneo\Platform\Syndication\Application\Common\Operation\OperationCollection;
use Akeneo\Platform\Syndication\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\Syndication\Application\Common\Source\PropertySource;
use Akeneo\Platform\Syndication\Application\Common\Source\SourceCollection;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Application\Common\Target\StringTarget;
use Akeneo\Platform\Syndication\Application\Common\ValueCollection;
use Akeneo\Platform\Syndication\Application\MapValues\MapValuesQueryHandler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class PropertyTestCase extends KernelTestCase
{
    public const PROPERTY_NAME = 'property_name';
    public const TARGET_NAME = 'test_column';

    public function setUp(): void
    {
        static::bootKernel(['debug' => false]);
    }

    protected function getMapValuesQueryHandler(): MapValuesQueryHandler
    {
        return static::$container->get('Akeneo\Platform\Syndication\Application\MapValues\MapValuesQueryHandler');
    }

    protected function createSingleSourceColumnCollection(array $operations, SelectionInterface $selection): ColumnCollection
    {
        $sourceCollection = SourceCollection::create([
            new PropertySource(
                sprintf('%s-uuid', self::PROPERTY_NAME),
                static::PROPERTY_NAME,
                null,
                null,
                OperationCollection::create($operations),
                $selection
            )
        ]);

        return ColumnCollection::create([
            new Column(
                new StringTarget(self::TARGET_NAME, 'string', false),
                $sourceCollection,
                new ConcatFormat(
                    ElementCollection::create([new SourceElement(sprintf('%s-uuid', self::PROPERTY_NAME))]),
                    false
                )
            )
        ]);
    }

    protected function createSingleValueValueCollection(SourceValueInterface $value): ValueCollection
    {
        $valueCollection = new ValueCollection();
        $valueCollection->add($value, static::PROPERTY_NAME, null, null);

        return $valueCollection;
    }
}
