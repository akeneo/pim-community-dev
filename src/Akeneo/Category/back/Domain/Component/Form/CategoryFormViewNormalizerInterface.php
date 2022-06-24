<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\Component\Form;

use Symfony\Component\Form\FormView;

interface CategoryFormViewNormalizerInterface
{
    public function normalizeFormView(FormView $formView): array;
}
