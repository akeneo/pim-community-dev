<?php

namespace Akeneo\Category\Infrastructure\Builder;

use Akeneo\Category\Domain\Model\Attribute\AttributeImage;
use Akeneo\Category\Domain\Model\Attribute\AttributeRichText;
use Akeneo\Category\Domain\Model\Attribute\AttributeText;
use Akeneo\Category\Domain\Model\Attribute\AttributeTextArea;
use Akeneo\Category\Domain\Model\Template;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsLocalizable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
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
        private GetCategoryInterface $getCategory
    ) {
    }

    /**
     * @param Code $categoryTreeCode
     * @param string $templateCode
     * @param LabelCollection $templateLabelCollection
     * @return Template
     * @throws \Exception
     */
    public function generateTemplate(
        Code $categoryTreeCode,
        string $templateCode,
        LabelCollection $templateLabelCollection
    ) : Template {
        $categoryTree = $this->getCategory->byCode((string) $categoryTreeCode);
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
                    AttributeIsLocalizable::fromBoolean(true),
                    LabelCollection::fromArray(['en_US' => 'Description']),
                    $templateUuid
                ),
                AttributeImage::create(
                    AttributeUuid::fromUuid(Uuid::uuid4()),
                    new AttributeCode('banner_image'),
                    AttributeOrder::fromInteger(2),
                    AttributeIsLocalizable::fromBoolean(false),
                    LabelCollection::fromArray(['en_US' => 'Banner image']),
                    $templateUuid
                ),
                AttributeText::create(
                    AttributeUuid::fromUuid(Uuid::uuid4()),
                    new AttributeCode('seo_meta_title'),
                    AttributeOrder::fromInteger(3),
                    AttributeIsLocalizable::fromBoolean(true),
                    LabelCollection::fromArray(['en_US' => 'SEO Meta Title']),
                    $templateUuid
                ),
                AttributeTextArea::create(
                    AttributeUuid::fromUuid(Uuid::uuid4()),
                    new AttributeCode('seo_meta_description'),
                    AttributeOrder::fromInteger(4),
                    AttributeIsLocalizable::fromBoolean(true),
                    LabelCollection::fromArray(['en_US' => 'SEO Meta Description']),
                    $templateUuid
                ),
                AttributeTextArea::create(
                    AttributeUuid::fromUuid(Uuid::uuid4()),
                    new AttributeCode('seo_keywords'),
                    AttributeOrder::fromInteger(5),
                    AttributeIsLocalizable::fromBoolean(true),
                    LabelCollection::fromArray(['en_US' => 'SEO Keywords']),
                    $templateUuid
                ),
            ])
        );
    }

    /**
     * Generate a template code by adding '_template' at the end of given category tree code
     * @param Code $categoryTreeCode
     * @return TemplateCode
     */
    private function generateTemplateCode(Code $categoryTreeCode) : TemplateCode
    {
        return new TemplateCode((string) $categoryTreeCode . '_template');
    }

    /**
     * Generate a template label by adding ' template' at the end of given category tree 'en_US' label
     * @param LabelCollection $categoryTreeLabelCollection
     * @return LabelCollection
     */
    private function generateTemplateLabelCollection(LabelCollection $categoryTreeLabelCollection) : LabelCollection
    {
        return LabelCollection::fromArray([
            'en_US' => $categoryTreeLabelCollection->getTranslation('en_US') . ' template'
        ]);
    }
}
