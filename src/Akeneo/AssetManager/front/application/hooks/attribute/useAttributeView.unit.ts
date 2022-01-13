import {renderHook} from '@testing-library/react-hooks';
import {useAttributeView} from './useAttributeView';
import {NormalizedTextAttribute, TextAttribute, ValidationRuleOption} from '../../../domain/model/attribute/type/text';
import {FakeConfigProvider} from '../../../../tests/front/unit/akeneoassetmanager/utils/FakeConfigProvider';
import {view as TextInputView} from 'akeneoassetmanager/application/component/attribute/edit/text';

const normalizedAttribute: NormalizedTextAttribute = {
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

const attribute: TextAttribute = {
  assetFamilyIdentifier: 'packshot',
  code: 'attribute_code',
  labelCollection: {},
  type: 'text',
  valuePerLocale: false,
  valuePerChannel: false,
  getCode: () => 'attribute_code',
  getAssetFamilyIdentifier: () => 'packshot',
  getType: () => 'text',
  getLabel: (_locale: string, _fallbackOnCode?: boolean) => 'attribute_code',
  getLabelCollection: () => ({}),
  normalize: () => normalizedAttribute,
  identifier: 'attribute_identifier',
  order: 2,
  isRequired: false,
  isReadOnly: false,
  equals: () => false,
  getIdentifier: () => 'attribute_identifier',
  maxLength: 1,
  isTextarea: false,
  isRichTextEditor: false,
  validationRule: ValidationRuleOption.None,
  regularExpression: null,
};

test('it throws when the provider is not found', () => {
  const {result} = renderHook(() => useAttributeView(attribute));

  expect(() => result.current).toThrowError('ConfigContext has not been properly initiated');
});

test('It throw when attribute view is not found', () => {
  const nonExistentAttributeType = {
    ...attribute,
    type: 'non_existent_attribute_type',
    getType: () => 'non_existent_attribute_type',
  };

  const {result} = renderHook(() => useAttributeView(nonExistentAttributeType), {wrapper: FakeConfigProvider});

  expect(() => result.current).toThrowError();
});

test('it returns the related attribute view', () => {
  const {result} = renderHook(() => useAttributeView(attribute), {wrapper: FakeConfigProvider});

  expect(result.current).toEqual(TextInputView);
});
