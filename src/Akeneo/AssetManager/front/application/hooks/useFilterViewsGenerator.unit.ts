import {renderHook} from '@testing-library/react-hooks';
import {useFilterViewsGenerator} from './useFilterViewsGenerator';
import {ValidationRuleOption} from '../../domain/model/attribute/type/text';
import {FakeConfigProvider} from '../../../tests/front/unit/akeneoassetmanager/utils/FakeConfigProvider';
import {filter as OptionFilter} from '../../application/component/asset/list/filter/option';

const normalizedAttributes = [
  {
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
  },
  {
    code: 'attribute_code',
    type: 'option',
    asset_family_identifier: 'packshot',
    labels: {},
    value_per_locale: false,
    value_per_channel: false,
    identifier: 'attribute_identifier',
    order: 2,
    is_required: false,
    is_read_only: false,
    options: [],
  },
];

test('it throws when the provider is not found', () => {
  const {result} = renderHook(() => useFilterViewsGenerator()(normalizedAttributes));

  expect(() => result.current).toThrowError('ConfigContext has not been properly initiated');
});

test('it returns the related filter views', () => {
  const {result} = renderHook(() => useFilterViewsGenerator()(normalizedAttributes), {wrapper: FakeConfigProvider});

  expect(result.current).toEqual([{view: OptionFilter, attribute: normalizedAttributes[1]}]);
});
