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

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Connector\Writer\Database;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Connector\Writer\Database\ProductAndProductModelDraftWriter;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Connector\Writer\Database\ProductDraftWriter;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use PhpSpec\ObjectBehavior;

class ProductAndProductModelDraftWriterSpec extends ObjectBehavior
{
    public function let(
        ProductDraftWriter $productDraftWriter,
        ProductDraftWriter $productModelDraftWriter
    ) {
        $this->beConstructedWith($productDraftWriter, $productModelDraftWriter);
    }

    public function it_is_the_product_and_product_model_drafts_database_writer()
    {
        $this->shouldImplement(ItemWriterInterface::class);
        $this->shouldImplement(StepExecutionAwareInterface::class);
        $this->shouldHaveType(ProductAndProductModelDraftWriter::class);
    }

    public function it_writes_product_drafts(ProductDraftWriter $productDraftWriter)
    {
        $productDraftA = new ProductDraft();
        $productDraftB = new ProductDraft();

        $productDraftWriter->write([$productDraftA, $productDraftB])->shouldBeCalled();

        $this->write([$productDraftA, $productDraftB]);
    }

    public function it_writes_product_model_drafts(ProductDraftWriter $productModelDraftWriter)
    {
        $productModelDraftA = new ProductModelDraft();
        $productModelDraftB = new ProductModelDraft();

        $productModelDraftWriter->write([$productModelDraftA, $productModelDraftB])->shouldBeCalled();

        $this->write([$productModelDraftA, $productModelDraftB]);
    }

    public function it_writes_product_an_product_model_drafts(
        ProductDraftWriter $productDraftWriter,
        ProductDraftWriter $productModelDraftWriter
    ) {
        $productDraftA = new ProductDraft();
        $productDraftB = new ProductDraft();
        $productModelDraft = new ProductModelDraft();

        $productDraftWriter->write([$productDraftA, $productDraftB])->shouldBeCalled();
        $productModelDraftWriter->write([$productModelDraft])->shouldBeCalled();

        $this->write([$productDraftA, $productModelDraft, $productDraftB]);
    }
}
