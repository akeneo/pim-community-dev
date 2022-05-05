<?php

namespace Akeneo\Category\Infrastructure\Controller\InternalApi;

use Akeneo\Category\Domain\Model\Template;
use Akeneo\Category\Domain\ValueObject\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\CategoryIdentifier;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\TemplateCode;
use Akeneo\Category\Domain\ValueObject\TemplateIdentifier;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetTemplate
{
    public function __invoke(Request $request): Response
    {
        $labels = LabelCollection::fromArray([
            'en_US' => 'a en_US label'
        ]);
        $attributes = new AttributeCollection([]);
        $template = new Template(
            new TemplateIdentifier($request->get('identifier')),
            new TemplateCode('a_template_code'),
            $labels,
            CategoryIdentifier::fromString($request->get('category_tree_identifier')),
            $attributes,
        );

        return new JsonResponse($template->normalize());
    }
}
