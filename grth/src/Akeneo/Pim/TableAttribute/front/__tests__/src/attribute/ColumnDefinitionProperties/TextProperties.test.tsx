import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {screen} from '@testing-library/react';
import TextProperties from '../../../../src/attribute/ColumDefinitionProperties/TextProperties';
import {getComplexTableAttribute} from '../../../factories';
import {TextColumnDefinition} from '../../../../src';
import {fireEvent} from '@testing-library/dom';

const selectedColumn = getComplexTableAttribute().table_configuration.find(
  columnDefinition => columnDefinition.data_type === 'text'
) as TextColumnDefinition;

describe('TextProperties', () => {
  it('should render the component', () => {
    renderWithProviders(
      <TextProperties attribute={getComplexTableAttribute()} selectedColumn={selectedColumn} handleChange={jest.fn()} />
    );

    expect(screen.getByText('pim_table_attribute.validations.max_length')).toBeInTheDocument();
  });

  it('should callback changes', () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TextProperties
        attribute={getComplexTableAttribute()}
        selectedColumn={selectedColumn}
        handleChange={handleChange}
      />
    );

    const maxLengthInput = screen.getByLabelText('pim_table_attribute.validations.max_length') as HTMLInputElement;
    fireEvent.change(maxLengthInput, {target: {value: '10'}});

    expect(handleChange).toBeCalledWith({
      ...selectedColumn,
      validations: {
        max_length: 10,
      },
    });
  });

  it('should display error message when max length is too big', () => {
    const erroredSelectedColumn = {...selectedColumn, validations: {max_length: 4000}};
    renderWithProviders(
      <TextProperties
        attribute={getComplexTableAttribute()}
        selectedColumn={erroredSelectedColumn}
        handleChange={jest.fn()}
      />
    );

    expect(screen.getByText('pim_table_attribute.validations.max_length_range')).toBeInTheDocument();
  });

  it('should unset validations when values are not filled anymore', () => {
    const filledSelectedColumn = {...selectedColumn, validations: {max_length: 50}};
    const handleChange = jest.fn();
    renderWithProviders(
      <TextProperties
        attribute={getComplexTableAttribute()}
        selectedColumn={filledSelectedColumn}
        handleChange={handleChange}
      />
    );

    const maxLengthInput = screen.getByLabelText('pim_table_attribute.validations.max_length') as HTMLInputElement;
    fireEvent.change(maxLengthInput, {target: {value: ''}});

    expect(handleChange).toBeCalledWith({...selectedColumn, validations: {max_length: undefined}});
  });
});
