import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {act, fireEvent, screen} from '@testing-library/react';
import {TableInputSelect} from '../../../../src/product/CellInputs/TableInputSelect';
import {getTableAttribute} from '../../factories/Attributes';

type EntryCallback = (entries: {isIntersecting: boolean}[]) => void;

let entryCallback: EntryCallback | undefined = undefined;
const intersectionObserverMock = (callback: EntryCallback) => ({
  observe: jest.fn(() => (entryCallback = callback)),
  unobserve: jest.fn(),
});
window.IntersectionObserver = jest.fn().mockImplementation(intersectionObserverMock);

const nutritionScoreOptions = [
  {code: 'A', labels: {en_US: 'A'}},
  {code: 'B', labels: {en_US: 'B'}},
  {code: 'C', labels: {en_US: 'C'}},
  {code: 'D', labels: {en_US: 'D'}},
  {code: 'E', labels: {en_US: 'E'}},
  {code: 'F', labels: {en_US: 'F'}},
  {code: 'G', labels: {en_US: 'G'}},
  {code: 'H', labels: {en_US: 'H'}},
  {code: 'I', labels: {en_US: 'I'}},
  {code: 'J', labels: {en_US: 'J'}},
  {code: 'K', labels: {en_US: 'K'}},
  {code: 'L', labels: {en_US: 'L'}},
  {code: 'M', labels: {en_US: 'M'}},
  {code: 'N', labels: {en_US: 'N'}},
  {code: 'O', labels: {en_US: 'O'}},
  {code: 'P', labels: {en_US: 'P'}},
  {code: 'Q', labels: {en_US: 'Q'}},
  {code: 'R', labels: {en_US: 'R'}},
  {code: 'S', labels: {en_US: 'S'}},
  {code: 'T', labels: {en_US: 'T'}},
  {code: 'U', labels: {en_US: 'U'}},
];

describe('TableInputSelect', () => {
  it('should render label of existing option', () => {
    renderWithProviders(
      <TableInputSelect
        value={'B'}
        options={nutritionScoreOptions}
        onChange={jest.fn()}
        attribute={getTableAttribute()}
      />
    );

    expect(screen.getByText('B')).toBeInTheDocument();
  });

  it('should delete the value', () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableInputSelect
        value={'B'}
        options={nutritionScoreOptions}
        onChange={handleChange}
        attribute={getTableAttribute()}
      />
    );

    fireEvent.click(screen.getByTitle('pim_common.clear'));
    expect(handleChange).toBeCalledWith(undefined);
  });

  it('should display nothing if no options', () => {
    const handleChange = jest.fn();
    renderWithProviders(<TableInputSelect value={'B'} onChange={handleChange} attribute={getTableAttribute()} />);

    expect(screen.queryByText('B')).not.toBeInTheDocument();
  });

  it('should paginate the options', async () => {
    renderWithProviders(
      <TableInputSelect onChange={jest.fn()} options={nutritionScoreOptions} attribute={getTableAttribute()} />
    );

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
      <TableInputSelect onChange={handleChange} options={nutritionScoreOptions} attribute={getTableAttribute()} />
    );

    await act(async () => {
      fireEvent.click(screen.getByTitle('pim_common.open'));
      expect(await screen.findByText('A')).toBeInTheDocument();
    });

    fireEvent.click(screen.getByText('A'));
    expect(handleChange).toBeCalledWith('A');
  });

  it('should search in the options', async () => {
    renderWithProviders(
      <TableInputSelect onChange={jest.fn()} options={nutritionScoreOptions} attribute={getTableAttribute()} />
    );

    await act(async () => {
      fireEvent.click(screen.getByTitle('pim_common.open'));
      expect(await screen.findByText('A')).toBeInTheDocument();
      fireEvent.change(screen.getByPlaceholderText('pim_common.search'), {target: {value: 'U'}});
    });

    expect(screen.queryByText('U')).toBeInTheDocument();
    expect(screen.queryByText('A')).not.toBeInTheDocument();
  });

  it('should display a link when there is no option', async () => {
    renderWithProviders(<TableInputSelect onChange={jest.fn()} options={[]} attribute={getTableAttribute()} />);

    await act(async () => {
      fireEvent.click(screen.getByTitle('pim_common.open'));
      expect(await screen.findByText('pim_table_attribute.form.product.no_add_options_link')).toBeInTheDocument();
      fireEvent.click(screen.getByText('pim_table_attribute.form.product.no_add_options_link'));
    });
  });
});
