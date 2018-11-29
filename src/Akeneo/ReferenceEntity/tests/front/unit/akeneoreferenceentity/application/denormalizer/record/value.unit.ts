import denormalize, {getValueDenormalizer} from 'akeneoreferenceentity/application/denormalizer/record/value';
import {createValue} from 'akeneoreferenceentity/domain/model/record/value';
import {denormalize as denormalizeTextAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/text';
import {denormalizeChannelReference} from 'akeneoreferenceentity/domain/model/channel-reference';
import {denormalizeLocaleReference} from 'akeneoreferenceentity/domain/model/locale-reference';
import {denormalize as denormalizeTextData} from 'akeneoreferenceentity/domain/model/record/data/text';

const normalizedDescription = {
  identifier: 'description_1234',
  reference_entity_identifier: 'designer',
  code: 'description',
  labels: {en_US: 'Description'},
  type: 'text',
  order: 0,
  value_per_locale: true,
  value_per_channel: false,
  is_required: true,
  max_length: 0,
  is_textarea: false,
  is_rich_text_editor: false,
  validation_rule: 'email',
  regular_expression: null,
};
const description = denormalizeTextAttribute(normalizedDescription);
const enUS = denormalizeLocaleReference('en_US');
const data = denormalizeTextData('a nice description');
const descriptionenUS = createValue(description, denormalizeChannelReference(null), enUS, data).normalize();

describe('akeneo > reference entity > application > denormalizer > record --- value', () => {
  test('I can denormalize a value', () => {
    const denormalizeValue = getValueDenormalizer(
      () => () => {
        return denormalizeTextData('a nice description');
      },
      () => denormalizeTextAttribute
    );
    expect(denormalizeValue(descriptionenUS).normalize()).toEqual(descriptionenUS);
  });

  test('I can execute the denormalizer', () => {
    expect(() => denormalize('a nice description').normalize()).toThrow();
  });
});
