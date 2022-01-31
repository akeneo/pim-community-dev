import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {DataMappingRow} from './DataMappingRow';
import {DataMapping} from '../../models';
import userEvent from '@testing-library/user-event';

const dataMapping: DataMapping = {
  uuid: '04839ab3-3ef3-4e80-8117-a2522552a20f',
  target: {
    code: 'sku',
    type: 'property',
    action: 'set',
    ifEmpty: 'skip',
    onError: 'skipLine',
  },
  sources: ['288d85cb-3ffb-432d-a422-d2c6810113ab', '68abfdcb-c91e-40e4-a928-fdfa7a31e8ab'],
  operations: [],
  sampleData: [],
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

test('it call handler when user click on row', () => {
  const handleClick = jest.fn();
  renderWithProviders(
    <table>
      <tbody>
        <DataMappingRow
          columns={columns}
          dataMapping={dataMapping}
          onClick={handleClick}
          hasError={false}
          isSelected={false}
        />
      </tbody>
    </table>
  );

  userEvent.click(screen.getByText('sku'));
  expect(handleClick).toHaveBeenCalledWith('04839ab3-3ef3-4e80-8117-a2522552a20f');
});

test('it displays a data mapping row', () => {
  renderWithProviders(
    <table>
      <tbody>
        <DataMappingRow
          dataMapping={dataMapping}
          onClick={jest.fn()}
          hasError={false}
          columns={columns}
          isSelected={false}
        />
      </tbody>
    </table>
  );

  expect(screen.queryByRole('alert')).not.toBeInTheDocument();
  expect(screen.getByText('sku')).toBeInTheDocument();
  expect(screen.getByText('akeneo.tailored_import.sources: Source 1 (A) Source 3 (C)')).toBeInTheDocument();
});

test('it displays a pill when there is an validation error', () => {
  renderWithProviders(
    <table>
      <tbody>
        <DataMappingRow
          dataMapping={dataMapping}
          onClick={jest.fn()}
          hasError={true}
          columns={columns}
          isSelected={false}
        />
      </tbody>
    </table>
  );

  expect(screen.getByRole('alert')).toBeInTheDocument();
});
