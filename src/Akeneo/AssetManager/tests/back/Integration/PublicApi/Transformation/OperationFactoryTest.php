<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Integration\PublicApi\Transformation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ColorspaceOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\OperationFactory;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\UnknownOperationException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class OperationFactoryTest extends KernelTestCase
{
    private ?object $operationFactory = null;

    public function setUp(): void
    {
        parent::setUp();
        static::bootKernel(['debug' => false]);
        $this->operationFactory = self::$container->get(OperationFactory::class);
    }

    public function test_it_returns_a_colorspace_operation()
    {
        $operation = $this->operationFactory->create('colorspace', ['colorspace' => 'grey']);
        $this->assertInstanceOf(ColorspaceOperation::class, $operation);
    }

    public function test_it_fails_with_unknown_type()
    {
        $this->expectException(UnknownOperationException::class);
        $this->operationFactory->create('unknown', ['width' => 100, 'height' => 80]);
    }
}
