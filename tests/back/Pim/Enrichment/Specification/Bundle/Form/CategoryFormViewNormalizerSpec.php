<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Form;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Form\FormView;

final class CategoryFormViewNormalizerSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith();
    }

    public function it_formats_the_category_form(FormView $formView)
    {
        $formView->children = [
            'label' => [],
        ];
        $normalizedForm = $this->normalizeFormView($formView);
    }
}
