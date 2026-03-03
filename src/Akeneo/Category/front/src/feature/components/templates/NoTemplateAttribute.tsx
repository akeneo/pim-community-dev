import {translate} from '@akeneo-pim-community/shared';
import {AttributesIllustration, Link, Placeholder} from 'akeneo-design-system';

export const NoTemplateAttribute = () => {
  return (
    <Placeholder
      title={translate('akeneo.category.edition_form.template.no_attribute_title')}
      illustration={<AttributesIllustration />}
      size="large"
    >
      {translate('akeneo.category.edition_form.template.no_attribute_instructions')}
      <Link
        href="https://help.akeneo.com/serenity-take-the-power-over-your-products/serenity-enrich-your-category"
        target="_blank"
      >
        {translate('akeneo.category.template.learn_more')}
      </Link>
    </Placeholder>
  );
};
