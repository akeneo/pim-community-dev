import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {act, screen, fireEvent} from '@testing-library/react';
import {RowSelector} from '../../../src/datagrid';
import {getComplexTableAttribute} from '../../factories';
import {ingredientsSelectOptions} from '../../../src/fetchers/__mocks__/SelectOptionsFetcher';

jest.mock('../../../src/fetchers/SelectOptionsFetcher');

type EntryCallback = (entries: {isIntersecting: boolean}[]) => void;
let entryCallback: EntryCallback | undefined = undefined;
const intersectionObserverMock = (callback: EntryCallback) => ({
  observe: jest.fn(() => (entryCallback = callback)),
  unobserve: jest.fn(),
});
window.IntersectionObserver = jest.fn().mockImplementation(intersectionObserverMock);

describe('RowSelector', () => {
  it('should display current row', async () => {
    renderWithProviders(
      <RowSelector attribute={getComplexTableAttribute()} value={ingredientsSelectOptions[1]} onChange={jest.fn()} />
    );

    expect(await screen.findByText('Pepper')).toBeInTheDocument();
  });

  it('should display all rows, then update it', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <RowSelector attribute={getComplexTableAttribute()} value={ingredientsSelectOptions[1]} onChange={handleChange} />
    );

    expect(await screen.findByText('Pepper')).toBeInTheDocument();

    act(() => {
      fireEvent.click(screen.getByTitle('pim_common.open'));
    });
    act(() => {
      entryCallback?.([{isIntersecting: true}]);
    });
    expect(screen.getByText('Salt')).toBeInTheDocument();
    expect(screen.getAllByText('Pepper')).toHaveLength(2);
    expect(screen.getByText('[eggs]')).toBeInTheDocument();
    expect(screen.getByText('Sugar')).toBeInTheDocument();
    fireEvent.click(screen.getByText('[eggs]'));
    expect(handleChange).toBeCalledWith(ingredientsSelectOptions[2]);
  });

  it('should remove current row', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <RowSelector attribute={getComplexTableAttribute()} value={ingredientsSelectOptions[1]} onChange={handleChange} />
    );

    expect(await screen.findByText('Pepper')).toBeInTheDocument();
    fireEvent.click(screen.getByTitle('pim_common.clear_value'));
    expect(handleChange).toBeCalledWith(undefined);
  });

  it('should select any row', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <RowSelector attribute={getComplexTableAttribute()} value={ingredientsSelectOptions[1]} onChange={handleChange} />
    );

    expect(await screen.findByText('Pepper')).toBeInTheDocument();
    act(() => {
      fireEvent.click(screen.getByTitle('pim_common.open'));
    });
    fireEvent.click(screen.getByText('pim_table_attribute.datagrid.any_row'));
    expect(handleChange).toBeCalledWith(null);
  });
});
