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

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\OperationFactory;
use PhpSpec\ObjectBehavior;

class OperationFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([DumbOperation::class]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(OperationFactory::class);
    }

    function it_creates_an_operation_given_a_type()
    {
        $this->create('dumb', [])->shouldBeAnInstanceOf(DumbOperation::class);
    }

    function it_fails_when_operation_type_is_not_found()
    {
        $this->shouldThrow(new \InvalidArgumentException('Operation "unknown" is unknown.'))
            ->during('create', ['unknown', []]);
    }
}

class DumbOperation implements Operation
{
    private function __construct()
    {
    }

    public static function getType(): string
    {
        return 'dumb';
    }

    public static function create(array $parameters): Operation
    {
        return new self();
    }

    public function normalize(): array
    {
        return [];
    }
}
