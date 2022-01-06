import React from 'react';
import {screen} from '@testing-library/react';
import {OPTION_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/option';
import {view as OptionView} from 'akeneoassetmanager/application/component/asset/edit/enrich/data/option';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {NormalizedOptionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/option';
import userEvent from '@testing-library/user-event';

const optionAttribute: NormalizedOptionAttribute = {
  code: 'tag',
  identifier: 'attribute_identifier',
  type: OPTION_ATTRIBUTE_TYPE,
  labels: {},
  order: 0,
  is_read_only: false,
  is_required: false,
  asset_family_identifier: 'packshot',
  value_per_channel: false,
  value_per_locale: false,
  options: [
    {
      code: 'tag_1',
      labels: {
        en_US: 'Tag 1',
      },
    },
    {
      code: 'tag_2',
      labels: {
        en_US: 'Tag 2',
      },
    },
  ],
};

const optionValue = {
  attribute: optionAttribute,
  channel: null,
  locale: null,
  data: 'tag_1',
};

test('It renders the option attribute', () => {
  renderWithProviders(
    <OptionView channel={null} value={optionValue} locale={null} onChange={jest.fn()} canEditData={true} />
  );

  expect(screen.getByText('[tag_1]')).toBeInTheDocument();
});

test('It renders the placeholder when the value is empty', () => {
  const emptyValue = {...optionValue, data: null};

  renderWithProviders(
    <OptionView channel={null} value={emptyValue} locale={null} onChange={jest.fn()} canEditData={true} />
  );

  expect(screen.getByPlaceholderText('pim_asset_manager.attribute.options.no_value')).toBeInTheDocument();
});

test('It does not render if the data is not a option data', () => {
  const otherValue = {...optionValue, attribute: {...optionValue.attribute, type: 'invalid_type'}};

  renderWithProviders(
    <OptionView channel={null} value={otherValue} locale={null} onChange={jest.fn()} canEditData={true} />
  );

  expect(screen.queryByText('[tag_1]')).not.toBeInTheDocument();
});

test('It can change the option value', () => {
  const onChange = jest.fn();

  renderWithProviders(
    <OptionView channel={null} value={optionValue} locale={null} onChange={onChange} canEditData={true} />
  );

  userEvent.click(screen.getByTitle('pim_common.open'));
  userEvent.click(screen.getByText('[tag_2]'));

  expect(onChange).toHaveBeenCalledWith({...optionValue, data: 'tag_2'});
  expect(onChange).toHaveBeenCalledTimes(1);
});
