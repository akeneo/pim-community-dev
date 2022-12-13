<?php

namespace Akeneo\Category\Infrastructure\Builder;

use Akeneo\Category\Domain\Model\Attribute\AttributeImage;
use Akeneo\Category\Domain\Model\Attribute\AttributeRichText;
use Akeneo\Category\Domain\Model\Attribute\AttributeText;
use Akeneo\Category\Domain\Model\Attribute\AttributeTextArea;
use Akeneo\Category\Domain\Model\Enrichment\Template;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeAdditionalProperties;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsLocalizable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsRequired;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsScopable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateCode;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Ramsey\Uuid\Uuid;

/**
 * @author    Adrien Migaire <adrien.migaire@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TemplateBuilder
{
    public function __construct(
        private GetCategoryInterface $getCategory,
    ) {
    }

    /**
     * @param Code $categoryTreeCode
     * @param string $templateCode
     *
     * @throws \Exception
     */
    public function generateTemplate(
        CategoryId $categoryTreeId,
        TemplateCode $templateCode,
        LabelCollection $templateLabelCollection,
    ): Template {
        $categoryTree = $this->getCategory->byId($categoryTreeId->getValue());
        $templateUuid = TemplateUuid::fromUuid(Uuid::uuid4());

        return new Template(
            $templateUuid,
            new TemplateCode($this->generateTemplateCode($categoryTree->getCode())),
            $this->generateTemplateLabelCollection($categoryTree->getLabels()),
            $categoryTree->getId(),
            AttributeCollection::fromArray([
                AttributeRichText::create(
                    AttributeUuid::fromUuid(Uuid::uuid4()),
                    new AttributeCode('description'),
                    AttributeOrder::fromInteger(1),
                    AttributeIsRequired::fromBoolean(true),
                    AttributeIsScopable::fromBoolean(true),
                    AttributeIsLocalizable::fromBoolean(true),
                    LabelCollection::fromArray(['en_US' => 'Description']),
                    $templateUuid,
                    AttributeAdditionalProperties::fromArray([]),
                ),
                AttributeImage::create(
                    AttributeUuid::fromUuid(Uuid::uuid4()),
                    new AttributeCode('banner_image'),
                    AttributeOrder::fromInteger(2),
                    AttributeIsRequired::fromBoolean(true),
                    AttributeIsScopable::fromBoolean(true),
                    AttributeIsLocalizable::fromBoolean(true),
                    LabelCollection::fromArray(['en_US' => 'Banner image']),
                    $templateUuid,
                    AttributeAdditionalProperties::fromArray([]),
                ),
                AttributeText::create(
                    AttributeUuid::fromUuid(Uuid::uuid4()),
                    new AttributeCode('seo_meta_title'),
                    AttributeOrder::fromInteger(3),
                    AttributeIsRequired::fromBoolean(true),
                    AttributeIsScopable::fromBoolean(true),
                    AttributeIsLocalizable::fromBoolean(true),
                    LabelCollection::fromArray(['en_US' => 'SEO Meta Title']),
                    $templateUuid,
                    AttributeAdditionalProperties::fromArray([]),
                ),
                AttributeTextArea::create(
                    AttributeUuid::fromUuid(Uuid::uuid4()),
                    new AttributeCode('seo_meta_description'),
                    AttributeOrder::fromInteger(4),
                    AttributeIsRequired::fromBoolean(true),
                    AttributeIsScopable::fromBoolean(true),
                    AttributeIsLocalizable::fromBoolean(true),
                    LabelCollection::fromArray(['en_US' => 'SEO Meta Description']),
                    $templateUuid,
                    AttributeAdditionalProperties::fromArray([]),
                ),
                AttributeTextArea::create(
                    AttributeUuid::fromUuid(Uuid::uuid4()),
                    new AttributeCode('seo_keywords'),
                    AttributeOrder::fromInteger(5),
                    AttributeIsRequired::fromBoolean(true),
                    AttributeIsScopable::fromBoolean(true),
                    AttributeIsLocalizable::fromBoolean(true),
                    LabelCollection::fromArray(['en_US' => 'SEO Keywords']),
                    $templateUuid,
                    AttributeAdditionalProperties::fromArray([]),
                ),
            ]),
        );
    }

    /**
     * Generate a template code by adding '_template' at the end of given category tree code.
     */
    private function generateTemplateCode(Code $categoryTreeCode): TemplateCode
    {
        return new TemplateCode((string) $categoryTreeCode.'_template');
    }

    /**
     * Generate a template label by adding ' template' at the end of given category tree 'en_US' label.
     */
    private function generateTemplateLabelCollection(?LabelCollection $categoryTreeLabelCollection): LabelCollection
    {
        $translations = [];
//        if ($categoryTreeLabelCollection) {
//            $translations = [
//                'en_US' => $categoryTreeLabelCollection->getTranslation('en_US').' template',
//            ];
//        }

        return LabelCollection::fromArray($translations);
    }
}
