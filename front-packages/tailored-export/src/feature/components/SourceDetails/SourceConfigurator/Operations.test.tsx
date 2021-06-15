import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {Operations} from './Operations';
import {Attribute, Source} from '../../../models';

const getAttribute = (type: string): Attribute => ({
  code: 'nice_attribute',
  type,
  labels: {},
  scopable: false,
  localizable: false,
});

jest.mock('../../../hooks/useAttributes', () => ({
  useAttribute: (attributeCode: string) => {
    switch (attributeCode) {
      case 'null':
        return null;
      default:
        return getAttribute(attributeCode);
    }
  },
}));

jest.mock('./Selector/Selector', () => ({
  Selector: ({onSelectionChange}: {onSelectionChange: () => void}) => (
    <button onClick={onSelectionChange}>This is a selector</button>
  ),
}));

test.each([
  'pim_catalog_text',
  'pim_catalog_textarea',
  'pim_catalog_identifier',
  'pim_catalog_boolean',
  'pim_catalog_number',
])('it renders a no operations placeholder for "%s" attribute', type => {
  const onSourceChange = jest.fn();

  const source: Source = {
    uuid: '22',
    code: type,
    channel: null,
    locale: null,
    operations: [],
    selection: {type: 'code'},
    type: 'attribute',
  };

  renderWithProviders(<Operations source={source} validationErrors={[]} onSourceChange={onSourceChange} />);

  expect(
    screen.getByText('akeneo.tailored_export.column_details.sources.no_source_configuration.title')
  ).toBeInTheDocument();
});

test.each([
  'pim_catalog_simpleselect',
  'pim_catalog_multiselect',
  'akeneo_reference_entity',
  'akeneo_reference_entity_collection',
  'pim_catalog_asset_collection',
])('it renders a selector for "%s" attribute', type => {
  const onSourceChange = jest.fn();

  const source: Source = {
    uuid: '22',
    code: type,
    channel: null,
    locale: null,
    operations: [],
    selection: {type: 'code'},
    type: 'attribute',
  };

  renderWithProviders(<Operations source={source} validationErrors={[]} onSourceChange={onSourceChange} />);

  userEvent.click(screen.getByText('This is a selector'));

  expect(onSourceChange).toHaveBeenCalled();
});

test('it renders nothing if the attribute is not found', () => {
  const onSourceChange = jest.fn();

  const source: Source = {
    uuid: '22',
    code: 'null',
    channel: null,
    locale: null,
    operations: [],
    selection: {type: 'code'},
    type: 'attribute',
  };

  renderWithProviders(<Operations source={source} validationErrors={[]} onSourceChange={onSourceChange} />);

  expect(
    screen.queryByText('akeneo.tailored_export.column_details.sources.no_source_configuration.title')
  ).not.toBeInTheDocument();
});
