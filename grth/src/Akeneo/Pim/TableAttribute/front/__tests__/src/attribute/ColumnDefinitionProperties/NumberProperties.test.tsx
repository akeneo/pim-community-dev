import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {screen} from '@testing-library/react';
import NumberProperties from '../../../../src/attribute/ColumDefinitionProperties/NumberProperties';
import {getComplexTableAttribute} from '../../../factories';
import {NumberColumnDefinition} from '../../../../src';
import {fireEvent} from '@testing-library/dom';

const selectedColumn = getComplexTableAttribute().table_configuration.find(
  columnDefinition => columnDefinition.data_type === 'number'
) as NumberColumnDefinition;

describe('NumberProperties', () => {
  it('should render the component', () => {
    renderWithProviders(
      <NumberProperties
        attribute={getComplexTableAttribute()}
        selectedColumn={selectedColumn}
        handleChange={jest.fn()}
      />
    );

    expect(screen.getByText('pim_table_attribute.validations.min')).toBeInTheDocument();
    expect(screen.getByText('pim_table_attribute.validations.max')).toBeInTheDocument();
    expect(screen.getByText('pim_table_attribute.validations.decimals_allowed')).toBeInTheDocument();
  });

  it('should callback changes', () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <NumberProperties
        attribute={getComplexTableAttribute()}
        selectedColumn={selectedColumn}
        handleChange={handleChange}
      />
    );

    const minInput = screen.getByLabelText('pim_table_attribute.validations.min') as HTMLInputElement;
    const maxInput = screen.getByLabelText('pim_table_attribute.validations.max') as HTMLInputElement;
    const decimalsAllowedCheckbox = screen.getByLabelText(
      'pim_table_attribute.validations.decimals_allowed'
    ) as HTMLInputElement;
    fireEvent.change(minInput, {target: {value: '10'}});
    fireEvent.change(maxInput, {target: {value: '50'}});
    fireEvent.click(decimalsAllowedCheckbox);

    expect(handleChange).toBeCalledWith({
      ...selectedColumn,
      validations: {
        min: 10,
        max: 50,
        decimals_allowed: true,
      },
    });
  });

  it('should display error message when min is greater than max', () => {
    const erroredSelectedColumn = {...selectedColumn, validations: {min: 50, max: 10}};
    renderWithProviders(
      <NumberProperties
        attribute={getComplexTableAttribute()}
        selectedColumn={erroredSelectedColumn}
        handleChange={jest.fn()}
      />
    );

    expect(screen.getByText('pim_table_attribute.validations.max_greater_than_min')).toBeInTheDocument();
  });

  it('should unset validations when values are not filled anymore', () => {
    const filledSelectedColumn = {
      ...selectedColumn,
      validations: {
        min: 50,
        max: 10,
        decimals_allowed: true,
      },
    };
    const handleChange = jest.fn();
    renderWithProviders(
      <NumberProperties
        attribute={getComplexTableAttribute()}
        selectedColumn={filledSelectedColumn}
        handleChange={handleChange}
      />
    );

    const minInput = screen.getByLabelText('pim_table_attribute.validations.min') as HTMLInputElement;
    const maxInput = screen.getByLabelText('pim_table_attribute.validations.max') as HTMLInputElement;
    const decimalsAllowedCheckbox = screen.getByLabelText(
      'pim_table_attribute.validations.decimals_allowed'
    ) as HTMLInputElement;
    fireEvent.change(minInput, {target: {value: ''}});
    fireEvent.change(maxInput, {target: {value: ''}});
    fireEvent.click(decimalsAllowedCheckbox);

    expect(handleChange).toBeCalledWith({
      ...selectedColumn,
      validations: {
        min: undefined,
        max: undefined,
        decimals_allowed: false,
      },
    });
  });
});
