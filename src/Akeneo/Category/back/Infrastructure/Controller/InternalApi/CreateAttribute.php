<?php

namespace Akeneo\Category\Infrastructure\Controller\InternalApi;

use Akeneo\Category\Domain\Model\Attribute;
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
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\TemplateIdentifier;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;


class CreateAttribute
{
    public function __construct()
    {
    }

    /**
     * Experiment the edition of a template with the submission of each partial action (create an attribute, edit, delete, ...)
     */
    public function __invoke(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        Assert::keyExists($data, 'type');
        Assert::keyExists($data, 'code');
        Assert::keyExists($data, 'template_identifier');

        // @todo create command related to the type of attribute
        // @todo validate the command data
        // @todo in case of invalid command data return the violation messages in JSON response with HTTP_BAD_REQUEST code (400)

        // @todo dispatch the creation command to the command bus

        return new JsonResponse($this->crateAttribute($data)->normalize());
    }

    private function crateAttribute(array $data): Attribute
    {
        // @todo delegate the creation of the Attribute objects to a factory
        switch ($data['type']) {
            case TextAttribute::ATTRIBUTE_TYPE:
                if ($data['is_textarea'] === true) {
                    return TextAttribute::createTextarea(
                        AttributeIdentifier::create($data['template_identifier'], $data['code'], Uuid::uuid4()->toString()),
                        new TemplateIdentifier($data['template_identifier']),
                        AttributeCode::fromString($data['code']),
                        LabelCollection::fromArray($data['labels']),
                        AttributeOrder::fromInteger(1),
                        AttributeIsRequired::fromBoolean(false),
                        AttributeValuePerChannel::fromBoolean($data['value_per_channel']),
                        AttributeValuePerLocale::fromBoolean($data['value_per_locale']),
                        AttributeMaxLength::noLimit(),
                        AttributeIsRichTextEditor::fromBoolean($data['is_rich_text_editor']),
                    );
                }
                return TextAttribute::createText(
                    AttributeIdentifier::create($data['template_identifier'], $data['code'], Uuid::uuid4()->toString()),
                    new TemplateIdentifier($data['template_identifier']),
                    AttributeCode::fromString($data['code']),
                    LabelCollection::fromArray($data['labels']),
                    AttributeOrder::fromInteger(1),
                    AttributeIsRequired::fromBoolean(false),
                    AttributeValuePerChannel::fromBoolean($data['value_per_channel']),
                    AttributeValuePerLocale::fromBoolean($data['value_per_locale']),
                    AttributeMaxLength::fromInteger($data['max_length'] ?? 255),
                    AttributeValidationRule::none(),
                    AttributeRegularExpression::createEmpty(),
                );
            default:
                throw new \LogicException(sprintf('Attribute type %s not supported', $data['type']), ['data' => $data]);
        }
    }
}
