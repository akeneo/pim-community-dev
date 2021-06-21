<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Category\Form;

use Symfony\Component\Form\FormView;

interface CategoryFormViewNormalizerInterface
{
    public function normalizeFormView(FormView $formView): array;
}
