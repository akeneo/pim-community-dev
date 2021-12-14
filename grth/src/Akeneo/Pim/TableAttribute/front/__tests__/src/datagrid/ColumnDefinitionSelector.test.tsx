import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {act, screen, fireEvent} from '@testing-library/react';
import {ColumnDefinitionSelector} from '../../../src';
import {getComplexTableAttribute} from '../../factories';
import {TestAttributeContextProvider} from '../../shared/TestAttributeContextProvider';

describe('ColumnDefinitionSelector', () => {
  it('should display current column', () => {
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <ColumnDefinitionSelector value={getComplexTableAttribute().table_configuration[0]} onChange={jest.fn()} />
      </TestAttributeContextProvider>
    );

    expect(screen.getByText('Ingredients')).toBeInTheDocument();
  });

  it('should display all columns, then update it', () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <ColumnDefinitionSelector value={getComplexTableAttribute().table_configuration[0]} onChange={handleChange} />
      </TestAttributeContextProvider>
    );

    act(() => {
      fireEvent.click(screen.getByTitle('pim_common.open'));
    });
    expect(screen.getAllByText('Ingredients')).toHaveLength(2);
    expect(screen.getByText('Quantity')).toBeInTheDocument();
    expect(screen.getByText('Is allergenic')).toBeInTheDocument();
    expect(screen.getByText('For 1 part')).toBeInTheDocument();
    expect(screen.getByText('Nutrition score')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Quantity'));
    expect(handleChange).toBeCalledWith(getComplexTableAttribute().table_configuration[1]);
  });
});
