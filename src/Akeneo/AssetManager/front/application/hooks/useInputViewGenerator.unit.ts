import {renderHook} from '@testing-library/react-hooks';
import {useInputViewGenerator} from './useInputViewGenerator';
import {NormalizedTextAttribute, ValidationRuleOption} from '../../domain/model/attribute/type/text';
import {FakeConfigProvider} from '../../../tests/front/unit/akeneoassetmanager/utils/FakeConfigProvider';
import {view as TextInputView} from '../../application/component/asset/edit/enrich/data/text';
import EditionValue from '../../domain/model/asset/edition-value';

const textAttribute: NormalizedTextAttribute = {
  code: 'attribute_code',
  type: 'text',
  asset_family_identifier: 'packshot',
  labels: {},
  value_per_locale: false,
  value_per_channel: false,
  identifier: 'attribute_identifier',
  order: 2,
  is_required: false,
  is_read_only: false,
  max_length: 1,
  is_textarea: false,
  is_rich_text_editor: false,
  validation_rule: ValidationRuleOption.None,
  regular_expression: null,
};

const editionValue: EditionValue = {
  data: 'data to render',
  channel: 'ecommerce',
  locale: 'en_US',
  attribute: textAttribute,
};

test('it throws when the provider is not found', () => {
  const {result} = renderHook(() => useInputViewGenerator()(editionValue));

  expect(() => result.current).toThrowError('ConfigContext has not been properly initiated');
});

test('It throw when attribute type is not found', () => {
  const nonExistentAttributeTypeEditionValue = {
    ...editionValue,
    attribute: {
      ...editionValue.attribute,
      type: 'non_existent_type',
    },
  };

  const {result} = renderHook(() => useInputViewGenerator()(nonExistentAttributeTypeEditionValue), {
    wrapper: FakeConfigProvider,
  });

  expect(() => result.current).toThrowError();
});

test('it returns the related input view', () => {
  const {result} = renderHook(() => useInputViewGenerator()(editionValue), {wrapper: FakeConfigProvider});

  expect(result.current).toEqual(TextInputView);
});
