import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {AttributeSelector} from './AttributeSelector';
import {Attribute} from '../../../../models';

const getAttribute = (type: string): Attribute => ({
  code: 'nice_attribute',
  type,
  labels: {},
  scopable: false,
  localizable: false,
  is_locale_specific: false,
  available_locales: [],
});

jest.mock('../../Selector/CodeLabelSelector', () => ({
  CodeLabelSelector: () => 'This is a code and label selector',
}));

jest.mock('../../Selector/CodeLabelCollectionSelector', () => ({
  CodeLabelCollectionSelector: () => 'This is a code and label collection selector',
}));

jest.mock('./MeasurementSelector', () => ({
  MeasurementSelector: () => 'This is a measurement selector',
}));

jest.mock('./DateSelector', () => ({
  DateSelector: () => 'This is a date selector',
}));

jest.mock('./PriceCollectionSelector', () => ({
  PriceCollectionSelector: () => 'This is a price collection selector',
}));

test.each([
  'pim_catalog_text',
  'pim_catalog_textarea',
  'pim_catalog_identifier',
  'pim_catalog_boolean',
  'pim_catalog_number',
])('it renders no selector for "%s" attribute', type => {
  const onSelectionChange = jest.fn();

  renderWithProviders(
    <AttributeSelector
      selection={{type: 'code'}}
      validationErrors={[]}
      attribute={getAttribute(type)}
      onSelectionChange={onSelectionChange}
    />
  );

  expect(screen.queryByText('pim_common.type')).not.toBeInTheDocument();
});

test.each(['pim_catalog_simpleselect', 'akeneo_reference_entity'])(
  'it renders a code label selector for "%s" attribute',
  type => {
    const onSelectionChange = jest.fn();

    renderWithProviders(
      <AttributeSelector
        selection={{type: 'code'}}
        validationErrors={[]}
        attribute={getAttribute(type)}
        onSelectionChange={onSelectionChange}
      />
    );

    expect(screen.getByText('This is a code and label selector')).toBeInTheDocument();
  }
);

test.each(['pim_catalog_multiselect', 'akeneo_reference_entity_collection', 'pim_catalog_asset_collection'])(
  'it renders a code label collection selector for "%s" attribute',
  type => {
    const onSelectionChange = jest.fn();

    renderWithProviders(
      <AttributeSelector
        validationErrors={[]}
        selection={{type: 'code'}}
        attribute={getAttribute(type)}
        onSelectionChange={onSelectionChange}
      />
    );

    expect(screen.getByText('This is a code and label collection selector')).toBeInTheDocument();
  }
);

test('it renders a measurement selector for measurement attribute', () => {
  const onSelectionChange = jest.fn();

  renderWithProviders(
    <AttributeSelector
      validationErrors={[]}
      selection={{type: 'code'}}
      attribute={getAttribute('pim_catalog_metric')}
      onSelectionChange={onSelectionChange}
    />
  );

  expect(screen.getByText('This is a measurement selector')).toBeInTheDocument();
});

test('it renders a price collection selector for price collection attribute', () => {
  const onSelectionChange = jest.fn();

  renderWithProviders(
    <AttributeSelector
      validationErrors={[]}
      selection={{type: 'amount'}}
      attribute={getAttribute('pim_catalog_price_collection')}
      onSelectionChange={onSelectionChange}
    />
  );

  expect(screen.getByText('This is a price collection selector')).toBeInTheDocument();
});

test('it renders a date selector for date attribute', () => {
  const onSelectionChange = jest.fn();

  renderWithProviders(
    <AttributeSelector
      validationErrors={[]}
      selection={{format: 'yyyy-mm-dd'}}
      attribute={getAttribute('pim_catalog_date')}
      onSelectionChange={onSelectionChange}
    />
  );

  expect(screen.getByText('This is a date selector')).toBeInTheDocument();
});
