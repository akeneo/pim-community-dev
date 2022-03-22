import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {DataMappingRow} from './DataMappingRow';
import {DataMapping} from '../../models';
import userEvent from '@testing-library/user-event';

const dataMapping: DataMapping = {
  uuid: '04839ab3-3ef3-4e80-8117-a2522552a20f',
  target: {
    code: 'parent',
    type: 'property',
    action_if_not_empty: 'set',
    action_if_empty: 'skip',
  },
  sources: ['288d85cb-3ffb-432d-a422-d2c6810113ab', '68abfdcb-c91e-40e4-a928-fdfa7a31e8ab'],
  operations: [],
  sample_data: [],
};

const columns = [
  {
    uuid: '288d85cb-3ffb-432d-a422-d2c6810113ab',
    index: 0,
    label: 'Source 1',
  },
  {
    uuid: '986c431c-08ac-4e59-b1b9-b036e2f37389',
    index: 1,
    label: 'Source 2',
  },
  {
    uuid: '68abfdcb-c91e-40e4-a928-fdfa7a31e8ab',
    index: 2,
    label: 'Source 3',
  },
];

test('it calls handler when user selects the row', () => {
  const handleSelect = jest.fn();

  renderWithProviders(
    <table>
      <tbody>
        <DataMappingRow
          columns={columns}
          dataMapping={dataMapping}
          onSelect={handleSelect}
          hasError={false}
          isSelected={false}
          isIdentifierDataMapping={false}
          onRemove={jest.fn()}
        />
      </tbody>
    </table>
  );

  userEvent.click(screen.getByText('parent'));
  expect(handleSelect).toHaveBeenCalledWith('04839ab3-3ef3-4e80-8117-a2522552a20f');
});

test('it calls remove handler after confirming removal', () => {
  const handleRemove = jest.fn();

  renderWithProviders(
    <table>
      <tbody>
        <DataMappingRow
          columns={columns}
          dataMapping={dataMapping}
          onSelect={jest.fn()}
          hasError={false}
          isSelected={false}
          isIdentifierDataMapping={false}
          onRemove={handleRemove}
        />
      </tbody>
    </table>
  );

  userEvent.click(screen.getByTitle('pim_common.remove'));
  userEvent.click(screen.getByText('pim_common.confirm'));

  expect(handleRemove).toHaveBeenCalledWith('04839ab3-3ef3-4e80-8117-a2522552a20f');
});

test('it does not display a remove button on identifier data mapping', () => {
  const handleRemove = jest.fn();

  renderWithProviders(
    <table>
      <tbody>
        <DataMappingRow
          columns={columns}
          dataMapping={dataMapping}
          onSelect={jest.fn()}
          hasError={false}
          isSelected={false}
          isIdentifierDataMapping={true}
          onRemove={handleRemove}
        />
      </tbody>
    </table>
  );

  expect(screen.queryByTitle('pim_common.remove')).not.toBeInTheDocument();
});

test('it displays a data mapping row', () => {
  renderWithProviders(
    <table>
      <tbody>
        <DataMappingRow
          dataMapping={dataMapping}
          onSelect={jest.fn()}
          hasError={false}
          columns={columns}
          isSelected={false}
          isIdentifierDataMapping={false}
          onRemove={jest.fn()}
        />
      </tbody>
    </table>
  );

  expect(screen.queryByRole('alert')).not.toBeInTheDocument();
  expect(screen.getByText('parent')).toBeInTheDocument();
  expect(
    screen.getByText('akeneo.tailored_import.data_mapping.sources.title: Source 1 (A) Source 3 (C)')
  ).toBeInTheDocument();
});

test('it displays a pill when there is a validation error', () => {
  renderWithProviders(
    <table>
      <tbody>
        <DataMappingRow
          dataMapping={dataMapping}
          onSelect={jest.fn()}
          hasError={true}
          columns={columns}
          isSelected={false}
          isIdentifierDataMapping={false}
          onRemove={jest.fn()}
        />
      </tbody>
    </table>
  );

  expect(screen.getByRole('alert')).toBeInTheDocument();
});
