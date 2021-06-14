import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {Selector} from './Selector';
import {Attribute} from '../../../../models';

const getAttribute = (type: string): Attribute => ({
  code: 'nice_attribute',
  type,
  labels: {},
  scopable: false,
  localizable: false,
});

jest.mock('./CodeLabelSelector', () => ({
  CodeLabelSelector: () => 'This is a code and label selector',
}));

jest.mock('./MeasurementSelector', () => ({
  MeasurementSelector: () => 'This is a measurement selector',
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
    <Selector selection={{type: 'code'}} attribute={getAttribute(type)} onSelectionChange={onSelectionChange} />
  );

  expect(screen.queryByText('pim_common.type')).not.toBeInTheDocument();
});

test.each([
  'pim_catalog_simpleselect',
  'pim_catalog_multiselect',
  'akeneo_reference_entity',
  'akeneo_reference_entity_collection',
  'pim_catalog_asset_collection',
])('it renders a code label selector for "%s" attribute', type => {
  const onSelectionChange = jest.fn();

  renderWithProviders(
    <Selector selection={{type: 'code'}} attribute={getAttribute(type)} onSelectionChange={onSelectionChange} />
  );

  expect(screen.getByText('This is a code and label selector')).toBeInTheDocument();
});

test('it renders a measurement selector for measurement attribute', () => {
  const onSelectionChange = jest.fn();

  renderWithProviders(
    <Selector
      selection={{type: 'code'}}
      attribute={getAttribute('pim_catalog_metric')}
      onSelectionChange={onSelectionChange}
    />
  );

  expect(screen.getByText('This is a measurement selector')).toBeInTheDocument();
});
