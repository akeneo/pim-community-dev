import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {act, fireEvent, screen} from '@testing-library/react';
import {default as TableInputSelectModule} from '../../../../src/product/CellInputs/SelectInput';
import {getTableAttribute} from '../../factories/Attributes';
import {ColumnDefinition} from '../../../../src/models';
import {getComplexTableConfiguration} from '../../factories/TableConfiguration';

jest.mock('../../../../src/fetchers/SelectOptionsFetcher');

type EntryCallback = (entries: {isIntersecting: boolean}[]) => void;

let entryCallback: EntryCallback | undefined = undefined;
const intersectionObserverMock = (callback: EntryCallback) => ({
  observe: jest.fn(() => (entryCallback = callback)),
  unobserve: jest.fn(),
});
window.IntersectionObserver = jest.fn().mockImplementation(intersectionObserverMock);

const nutritionScoreColumn: ColumnDefinition = {
  code: 'nutrition_score',
  validations: {},
  data_type: 'select',
  labels: {},
};

const TableInputSelect = TableInputSelectModule.renderer;

describe('TableInputSelect', () => {
  it('should render label of existing option', async () => {
    renderWithProviders(
      <TableInputSelect
        columnDefinition={nutritionScoreColumn}
        highlighted={false}
        inError={false}
        row={{'unique id': 'uniqueIdB', nutrition_score: 'B'}}
        onChange={jest.fn()}
        attribute={{...getTableAttribute(), table_configuration: getComplexTableConfiguration()}}
        translate={a => a}
      />
    );

    expect(await screen.findByText('B')).toBeInTheDocument();
  });

  it('should delete the value', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableInputSelect
        columnDefinition={nutritionScoreColumn}
        highlighted={false}
        inError={false}
        row={{'unique id': 'uniqueIdB', nutrition_score: 'B'}}
        onChange={handleChange}
        attribute={{...getTableAttribute(), table_configuration: getComplexTableConfiguration()}}
        translate={a => a}
      />
    );
    expect(await screen.findByText('B')).toBeInTheDocument();

    fireEvent.click(screen.getByTitle('pim_common.clear'));
    expect(handleChange).toBeCalledWith(undefined);
  });

  it('should display nothing if no options', () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableInputSelect
        columnDefinition={{...nutritionScoreColumn, code: 'no_options'}}
        highlighted={false}
        inError={false}
        row={{'unique id': 'uniqueIdB', no_options: 'B'}}
        onChange={handleChange}
        attribute={{...getTableAttribute(), table_configuration: getComplexTableConfiguration()}}
        translate={a => a}
      />
    );

    expect(screen.queryByText('B')).not.toBeInTheDocument();
  });

  it('should paginate the options', async () => {
    renderWithProviders(
      <TableInputSelect
        columnDefinition={nutritionScoreColumn}
        highlighted={false}
        inError={false}
        row={{'unique id': 'uniqueIdB', nutrition_score: 'B'}}
        onChange={jest.fn()}
        attribute={{...getTableAttribute(), table_configuration: getComplexTableConfiguration()}}
        translate={a => a}
      />
    );
    expect(await screen.findByText('B')).toBeInTheDocument();

    await act(async () => {
      fireEvent.click(screen.getByTitle('pim_common.open'));
      expect(await screen.findByText('A')).toBeInTheDocument();
    });

    expect(screen.queryByText('U')).not.toBeInTheDocument();

    act(() => {
      entryCallback?.([{isIntersecting: true}]);
    });
    expect(await screen.findByText('U')).toBeInTheDocument();
  });

  it('should updates the value', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableInputSelect
        columnDefinition={nutritionScoreColumn}
        highlighted={false}
        inError={false}
        row={{'unique id': 'uniqueIdB', nutrition_score: 'B'}}
        onChange={handleChange}
        attribute={{...getTableAttribute(), table_configuration: getComplexTableConfiguration()}}
        translate={a => a}
      />
    );
    expect(await screen.findByText('B')).toBeInTheDocument();

    await act(async () => {
      fireEvent.click(screen.getByTitle('pim_common.open'));
      expect(await screen.findByText('A')).toBeInTheDocument();
    });

    fireEvent.click(screen.getByText('A'));
    expect(handleChange).toBeCalledWith('A');
  });

  it('should search in the options', async () => {
    renderWithProviders(
      <TableInputSelect
        columnDefinition={nutritionScoreColumn}
        highlighted={false}
        inError={false}
        row={{'unique id': 'uniqueIdB', nutrition_score: 'B'}}
        onChange={jest.fn()}
        attribute={{...getTableAttribute(), table_configuration: getComplexTableConfiguration()}}
        translate={a => a}
      />
    );
    expect(await screen.findByText('B')).toBeInTheDocument();

    await act(async () => {
      fireEvent.click(screen.getByTitle('pim_common.open'));
      expect(await screen.findByText('A')).toBeInTheDocument();
      fireEvent.change(screen.getByPlaceholderText('pim_common.search'), {target: {value: 'U'}});
    });

    expect(screen.queryByText('U')).toBeInTheDocument();
    expect(screen.queryByText('A')).not.toBeInTheDocument();
  });

  it('should display a link when there is no option', async () => {
    const table_configuration = getComplexTableConfiguration();
    table_configuration[4].code = 'no_options';
    renderWithProviders(
      <TableInputSelect
        columnDefinition={{...nutritionScoreColumn, code: 'no_options'}}
        highlighted={false}
        inError={false}
        row={{'unique id': 'uniqueIdB'}}
        onChange={jest.fn()}
        attribute={{...getTableAttribute(), table_configuration}}
        translate={a => a}
      />
    );
    expect(await screen.findByTitle('pim_common.open')).toBeInTheDocument();

    await act(async () => {
      fireEvent.click(screen.getByTitle('pim_common.open'));
      expect(await screen.findByText('pim_table_attribute.form.product.no_add_options_link')).toBeInTheDocument();
      fireEvent.click(screen.getByText('pim_table_attribute.form.product.no_add_options_link'));
    });
  });
});
