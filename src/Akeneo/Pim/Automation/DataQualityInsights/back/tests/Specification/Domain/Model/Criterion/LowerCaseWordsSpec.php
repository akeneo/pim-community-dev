<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Criterion;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PhpSpec\ObjectBehavior;

class LowerCaseWordsSpec extends ObjectBehavior
{
    public function it_evaluates_a_product_value_without_errors()
    {
        $productValue =
'Julia’s input: Nothing says luxury quite like Scott. 
A sleek silhouette with pulled detail cushions, upholstered in plush velvet – it oozes sophistication. 
The clean lines nod to mid-century design, and there’s plenty of room to snuggle up.';

        $this->evaluate($productValue)->shouldBeLike(new Rate(100));
    }

    public function it_evaluates_a_product_value_with_one_error()
    {
        $productValue =
'Julia’s input: Nothing says luxury quite like Scott.Perry.
A sleek silhouette with pulled detail cushions, upholstered in plush velvet – it oozes sophistication.
the clean lines nod to mid-century design, and there’s plenty of room to snuggle up.';

        $this->evaluate($productValue)->shouldBeLike(new Rate(76));
    }

    public function it_evaluates_a_product_value_with_two_errors()
    {
        $productValue =
'Julia’s input: nothing says luxury quite like Scott. 
A sleek silhouette with pulled detail cushions. upholstered in plush velvet – it oozes sophistication
the clean lines nod to mid-century design, and there’s plenty of room to snuggle up.';

        $this->evaluate($productValue)->shouldBeLike(new Rate(52));
    }

    public function it_evaluates_a_product_value_with_three_errors()
    {
        $productValue =
'  julia’s input: nothing says luxury quite like Scott. 
A sleek silhouette with pulled detail cushions, upholstered in plush velvet – it oozes sophistication. 
the clean lines nod to mid-century design, and there’s plenty of room to snuggle up.';

        $this->evaluate($productValue)->shouldBeLike(new Rate(28));
    }

    public function it_evaluates_a_product_value_with_four_errors()
    {
        $productValue =
'julia’s input? nothing says luxury quite like Scott!
a sleek silhouette with pulled detail cushions, upholstered in plush velvet – it oozes sophistication...
the clean lines nod to mid-century design; and there’s plenty of room to snuggle up.';

        $this->evaluate($productValue)->shouldBeLike(new Rate(4));
    }

    public function it_evaluates_a_product_value_with_more_than_four_errors()
    {
        $productValue =
'julia’s input? nothing says luxury quite like Scott!
a sleek silhouette with pulled detail cushions. upholstered in plush velvet – it oozes sophistication...
the clean lines nod to mid-century design; and there’s plenty of room to snuggle up.';

        $this->evaluate($productValue)->shouldBeLike(new Rate(0));
    }
}
