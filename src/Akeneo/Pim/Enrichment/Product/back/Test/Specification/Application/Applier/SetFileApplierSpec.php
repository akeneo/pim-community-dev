<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFile;
use Akeneo\Pim\Enrichment\Product\Application\Applier\SetFileApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;

class SetFileApplierSpec extends ObjectBehavior
{
    function let(ObjectUpdaterInterface $updater)
    {
        $this->beConstructedWith($updater);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SetFileApplier::class);
        $this->shouldImplement(UserIntentApplier::class);
    }

    function it_applies_set_file_user_intent(ObjectUpdaterInterface $updater): void
    {
        $product = new Product();
        $setFile = new SetFile('myImage', '/path/to/a/file');
        $updater->update(
            $product,
            [
                'values' => [
                    'myImage' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => ['filePath' => '/path/to/a/file'],
                        ],
                    ],
                ],
            ]
        )->shouldBeCalledOnce();

        $this->apply($setFile, $product, 1);
    }

    function it_throws_an_exception_when_user_intent_is_not_supported(): void
    {
        $product = new Product();
        $setEnabledUserIntent = new SetEnabled(true);
        $this->shouldThrow(\InvalidArgumentException::class)->during('apply', [$setEnabledUserIntent, $product, 1]);
    }
}
