import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {act, fireEvent, screen} from '@testing-library/react';
import {TableInputValue, UNIQUE_ID_KEY} from '../../../src';
import {getComplexTableAttribute, getTableValueWithId} from '../../factories';
import {dragAndDrop} from '../../shared/dragAndDrop';
import {TestAttributeContextProvider} from '../../shared/TestAttributeContextProvider';
import {mockScroll} from '../../shared/mockScroll';

jest.mock('../../../src/attribute/LocaleLabel');
jest.mock('../../../src/fetchers/SelectOptionsFetcher');
jest.mock('../../../src/fetchers/RecordFetcher');
jest.mock('../../../src/fetchers/MeasurementFamilyFetcher');
mockScroll();

describe('TableInputValue', () => {
  it('should render the component', async () => {
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <TableInputValue
          valueData={getTableValueWithId()}
          searchText={''}
          onChange={jest.fn()}
          visibility={'CAN_EDIT'}
        />
      </TestAttributeContextProvider>
    );
    await act(async () => {
      expect(await screen.findByText('Sugar')).toBeInTheDocument();
    });

    expect(screen.getByText('Ingredients')).toBeInTheDocument();
    expect(screen.getByText('Quantity')).toBeInTheDocument();
    expect(screen.getByText('For 1 part')).toBeInTheDocument();
    expect(screen.getByText('Is allergenic')).toBeInTheDocument();
    ['quantity', 'part', 'is_allergenic'].forEach(columnCode => {
      ['uniqueidsugar', 'uniqueidsalt', 'uniqueidcaramel'].forEach(uniqueId => {
        expect(screen.getByTestId(`input-${uniqueId}-${columnCode}`)).toBeInTheDocument();
      });
    });
  });

  it('should callback changes', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <TableInputValue
          valueData={getTableValueWithId()}
          searchText={''}
          onChange={handleChange}
          visibility={'CAN_EDIT'}
        />
      </TestAttributeContextProvider>
    );
    await act(async () => {
      expect(await screen.findByText('Sugar')).toBeInTheDocument();
    });

    expect(screen.getAllByText('pim_common.yes')).toHaveLength(1);
    expect(screen.getAllByText('pim_common.no')).toHaveLength(1);
    // 3 lines, 1 open for boolean, 1 for select and 1 for measurement
    expect(screen.getAllByTitle('pim_common.open')).toHaveLength(9);

    fireEvent.click(screen.getAllByTitle('pim_common.clear')[1]); // Clear sugar nutrition score
    fireEvent.change(screen.getByTestId('input-uniqueidsugar-quantity'), {target: {value: '200'}});
    fireEvent.change(screen.getByTestId('input-uniqueidsalt-part'), {target: {value: '42kg'}});
    act(() => {
      fireEvent.click(screen.getAllByTitle('pim_common.open')[2]); // Opens the measurement unit
    });
    fireEvent.click(screen.getByText('Ah'));
    act(() => {
      fireEvent.click(screen.getAllByTitle('pim_common.open')[6]); // Opens the caramel boolean
    });
    fireEvent.click(screen.getAllByText('pim_common.yes')[1]);

    expect(handleChange).toBeCalledWith([
      {
        ...getTableValueWithId()[0],
        quantity: '200',
        nutrition_score: undefined,
        ElectricCharge: {amount: 10, unit: 'AMPEREHOUR'},
      },
      {...getTableValueWithId()[1], part: '42kg'},
      {...getTableValueWithId()[2], is_allergenic: true},
    ]);
  });

  it('should search', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <TableInputValue
          valueData={getTableValueWithId()}
          searchText={'r'}
          onChange={handleChange}
          visibility={'CAN_EDIT'}
        />
      </TestAttributeContextProvider>
    );
    await act(async () => {
      expect(await screen.findByText('Sugar')).toBeInTheDocument();
    });

    ['quantity', 'part', 'is_allergenic'].forEach(columnCode => {
      expect(screen.queryByTestId(`input-uniquesalt-${columnCode}`)).not.toBeInTheDocument();
    });
  });

  it('should change inputValue class when a violated cell changed', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <TableInputValue
          valueData={getTableValueWithId()}
          onChange={handleChange}
          violatedCells={[
            {
              id: 'uniqueidsugar',
              columnCode: 'quantity',
            },
          ]}
          visibility={'CAN_EDIT'}
        />
      </TestAttributeContextProvider>
    );
    await act(async () => {
      expect(await screen.findByText('Sugar')).toBeInTheDocument();
    });

    const sugarInput = screen.getByTestId('input-uniqueidsugar-quantity');
    const formerClassList = sugarInput.classList.toString();

    act(() => {
      fireEvent.change(sugarInput, {target: {value: '200'}});
    });
    expect(sugarInput.classList.toString()).not.toEqual(formerClassList);
  });

  it('should delete row', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <TableInputValue
          valueData={getTableValueWithId()}
          searchText={''}
          onChange={handleChange}
          visibility={'CAN_EDIT'}
        />
      </TestAttributeContextProvider>
    );
    await act(async () => {
      expect(await screen.findByText('Sugar')).toBeInTheDocument();
    });

    fireEvent.click(screen.getAllByTitle('pim_common.actions')[1]);
    const deleteRow = await screen.findByTitle('pim_table_attribute.form.product.actions.delete_row');
    expect(deleteRow).toBeInTheDocument();
    fireEvent.click(deleteRow);
    expect(handleChange).toBeCalledWith([{...getTableValueWithId()[0]}, {...getTableValueWithId()[2]}]);
  });

  it('should clear row', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <TableInputValue
          valueData={getTableValueWithId()}
          searchText={''}
          onChange={handleChange}
          visibility={'CAN_EDIT'}
        />
      </TestAttributeContextProvider>
    );
    await act(async () => {
      expect(await screen.findByText('Sugar')).toBeInTheDocument();
    });

    fireEvent.click(screen.getAllByTitle('pim_common.actions')[1]);
    const clearRow = await screen.findByTitle('pim_table_attribute.form.product.actions.clear_row');
    expect(clearRow).toBeInTheDocument();
    fireEvent.click(clearRow);
    expect(handleChange).toBeCalledWith([
      {...getTableValueWithId()[0]},
      {...getTableValueWithId()[1], part: undefined, is_allergenic: undefined, nutrition_score: undefined},
      {...getTableValueWithId()[2]},
    ]);
  });

  it('should move row to first position', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <TableInputValue
          valueData={getTableValueWithId()}
          searchText={''}
          onChange={handleChange}
          visibility={'CAN_EDIT'}
        />
      </TestAttributeContextProvider>
    );
    await act(async () => {
      expect(await screen.findByText('Sugar')).toBeInTheDocument();
    });

    fireEvent.click(screen.getAllByTitle('pim_common.actions')[1]);
    const moveFirst = await screen.findByTitle('pim_table_attribute.form.product.actions.move_first');
    expect(moveFirst).toBeInTheDocument();
    fireEvent.click(moveFirst);
    expect(handleChange).toBeCalledWith([
      {...getTableValueWithId()[1]},
      {...getTableValueWithId()[0]},
      {...getTableValueWithId()[2]},
    ]);
  });

  it('should move row to last position', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <TableInputValue
          valueData={getTableValueWithId()}
          searchText={''}
          onChange={handleChange}
          visibility={'CAN_EDIT'}
        />
      </TestAttributeContextProvider>
    );
    await act(async () => {
      expect(await screen.findByText('Sugar')).toBeInTheDocument();
    });

    fireEvent.click(screen.getAllByTitle('pim_common.actions')[1]);
    const moveLast = await screen.findByTitle('pim_table_attribute.form.product.actions.move_last');
    expect(moveLast).toBeInTheDocument();
    fireEvent.click(moveLast);
    expect(handleChange).toBeCalledWith([
      {...getTableValueWithId()[0]},
      {...getTableValueWithId()[2]},
      {...getTableValueWithId()[1]},
    ]);
  });

  it('should reorder rows', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <TableInputValue
          valueData={getTableValueWithId()}
          searchText={''}
          onChange={handleChange}
          visibility={'CAN_EDIT'}
        />
      </TestAttributeContextProvider>
    );
    await act(async () => {
      expect(await screen.findByText('Sugar')).toBeInTheDocument();
    });

    dragAndDrop(0, 3);

    expect(handleChange).toBeCalledWith([
      {...getTableValueWithId()[1]},
      {...getTableValueWithId()[2]},
      {...getTableValueWithId()[0]},
    ]);
  });

  it('should not render anything if select cell inputs are undefined', async () => {
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <TableInputValue
          valueData={getTableValueWithId()}
          searchText={''}
          onChange={jest.fn()}
          visibility={'CAN_EDIT'}
        />
      </TestAttributeContextProvider>
    );
    expect(await screen.findByText('Ingredients')).toBeInTheDocument();

    expect(screen.queryByText('100')).not.toBeInTheDocument(); // This is the Quantity cell of Sugar line
  });

  it('should not render anything if record cell inputs are undefined', () => {
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute('reference_entity')}>
        <TableInputValue
          valueData={getTableValueWithId('reference_entity')}
          searchText={''}
          onChange={jest.fn()}
          visibility={'CAN_EDIT'}
        />
      </TestAttributeContextProvider>
    );
    expect(screen.queryByText('Vannes')).not.toBeInTheDocument();
  });

  it('should render records as fist column', async () => {
    const valueDataWithUnknownRecord = getTableValueWithId('reference_entity');
    valueDataWithUnknownRecord.push({
      [UNIQUE_ID_KEY]: 'unknown_record_uniqueid',
      city: 'unknown_record',
    });
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute('reference_entity')}>
        <TableInputValue valueData={valueDataWithUnknownRecord} visibility={'CAN_EDIT'} />
      </TestAttributeContextProvider>
    );

    expect(await screen.findByText('Vannes')).toBeInTheDocument();
    expect(await screen.findByText('Nantes')).toBeInTheDocument();
    expect(await screen.findByText('Brest')).toBeInTheDocument();
    expect(await screen.findByText('[unknown_record]')).toBeInTheDocument();
  });
});
