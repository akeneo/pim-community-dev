import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {act, screen, fireEvent} from '@testing-library/react';
import {RowSelector} from '../../../src/datagrid';
import {getComplexTableAttribute} from '../../factories';
import {ingredientsSelectOptions} from '../../../src/fetchers/__mocks__/SelectOptionsFetcher';

jest.mock('../../../src/fetchers/SelectOptionsFetcher');

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
    expect(screen.getByText('Salt')).toBeInTheDocument();
    expect(screen.getAllByText('Pepper')).toHaveLength(2);
    expect(screen.getByText('[eggs]')).toBeInTheDocument();
    expect(screen.getByText('Sugar')).toBeInTheDocument();
    fireEvent.click(screen.getByText('[eggs]'));
    expect(handleChange).toBeCalledWith(ingredientsSelectOptions[2]);
  });
});
