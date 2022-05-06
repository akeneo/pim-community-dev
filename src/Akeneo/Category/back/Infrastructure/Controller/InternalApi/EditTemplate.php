<?php

namespace Akeneo\Category\Infrastructure\Controller\InternalApi;

use Akeneo\Category\API\UpsertCategoryTemplateCommand;
use Akeneo\Category\Domain\Model\Attribute\TextAttribute;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIdentifier;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsRequired;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsRichTextEditor;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeMaxLength;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeRegularExpression;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeValidationRule;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeValuePerChannel;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeValuePerLocale;
use Akeneo\Category\Domain\ValueObject\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\CategoryIdentifier;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\TemplateCode;
use Akeneo\Category\Domain\ValueObject\TemplateIdentifier;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EditTemplate
{
    public function __construct()
    {
    }

    /**
     * Experiment the edition of a template with the submission of the whole template data
     */
    public function __invoke(Request $request): Response
    {
        $command = $this->buildCommand($request->getContent());
        /*$violations = $this->validator->validate($command);

        if ($violations->count() > 0) {
            return new JsonResponse(
                $this->serializer->normalize($violations, 'internal_api'),
                Response::HTTP_BAD_REQUEST
            );
        }*/

        // @todo validate the command data and return violation errors in response with Error code
        // @todo dispatch command to the command bus

        return new JsonResponse($command->getTemplate()->normalize(), Response::HTTP_CREATED);
    }

    private function buildCommand(string $jsonContent): UpsertCategoryTemplateCommand
    {
        $normalizedTemplate = json_decode($jsonContent, true);

        return new UpsertCategoryTemplateCommand(
            $normalizedTemplate['identifier'] === null,
            new TemplateIdentifier($normalizedTemplate['identifier'] ?? sprintf('a_generated_template_id_%d', time())),
            new TemplateCode($normalizedTemplate['code']),
            CategoryIdentifier::fromString($normalizedTemplate['category_tree_identifier']),
            LabelCollection::fromArray($normalizedTemplate['labels']),
            new AttributeCollection($this->buildAttributes($normalizedTemplate['attributes'] ?? []))
        );
    }

    private function buildAttributes(array $normalizedAttributes): array
    {
        // @todo delegate the creation of attributes to a factory
        $attributes = [];

        foreach ($normalizedAttributes as $normalizedAttribute) {
            if ($normalizedAttribute['type'] !== TextAttribute::ATTRIBUTE_TYPE) {
                continue;
            }

            if ($normalizedAttribute['is_textarea'] === true) {
                $attributes[] = TextAttribute::createTextarea(
                    AttributeIdentifier::create($normalizedAttribute['template_identifier'], $normalizedAttribute['code'], Uuid::uuid4()->toString()),
                    new TemplateIdentifier($normalizedAttribute['template_identifier']),
                    AttributeCode::fromString($normalizedAttribute['code']),
                    LabelCollection::fromArray($normalizedAttribute['labels']),
                    AttributeOrder::fromInteger(1),
                    AttributeIsRequired::fromBoolean(false),
                    AttributeValuePerChannel::fromBoolean($normalizedAttribute['value_per_channel']),
                    AttributeValuePerLocale::fromBoolean($normalizedAttribute['value_per_locale']),
                    AttributeMaxLength::noLimit(),
                    AttributeIsRichTextEditor::fromBoolean($normalizedAttribute['is_rich_text_editor']),
                );
            }
            
            $attributes[] = TextAttribute::createText(
                AttributeIdentifier::create($normalizedAttribute['template_identifier'], $normalizedAttribute['code'], Uuid::uuid4()->toString()),
                new TemplateIdentifier($normalizedAttribute['template_identifier']),
                AttributeCode::fromString($normalizedAttribute['code']),
                LabelCollection::fromArray($normalizedAttribute['labels']),
                AttributeOrder::fromInteger(1),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean($normalizedAttribute['value_per_channel']),
                AttributeValuePerLocale::fromBoolean($normalizedAttribute['value_per_locale']),
                AttributeMaxLength::fromInteger($normalizedAttribute['max_length'] ?? 255),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty(),
            );
        }
        return $attributes;
    }
}
