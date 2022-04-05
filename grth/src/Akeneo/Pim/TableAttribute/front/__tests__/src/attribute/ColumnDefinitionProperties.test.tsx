import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {ColumnDefinitionProperties} from '../../../src';
import {fireEvent, screen} from '@testing-library/react';
import {
  columnDefinitionPropertiesMapping,
  getComplexTableAttribute,
  getTableAttributeWithoutColumn,
  getEnUsLocale,
  getNumberColumnDefinitionWithId,
  getSelectColumnDefinitionWithId,
} from '../../factories';

jest.mock('../../../src/attribute/ManageOptionsModal');

describe('ColumnDefinitionProperties', () => {
  it('should render the component', () => {
    renderWithProviders(
      <ColumnDefinitionProperties
        selectedColumn={getSelectColumnDefinitionWithId()}
        onChange={jest.fn()}
        activeLocales={[getEnUsLocale()]}
        catalogLocaleCode={'en_US'}
        isDuplicateColumnCode={() => false}
        savedColumnIds={[]}
        attribute={getComplexTableAttribute()}
        columnDefinitionPropertiesMapping={columnDefinitionPropertiesMapping}
      />
    );

    expect(screen.getByText(/pim_table_attribute.form.attribute.column_code/)).toBeInTheDocument();
    expect(screen.getByText(/pim_table_attribute.form.attribute.data_type/)).toBeInTheDocument();
    expect(screen.getByText(/pim_table_attribute.form.attribute.labels/)).toBeInTheDocument();

    const requiredForCompletenessInput = screen.getByLabelText(
      'pim_table_attribute.form.attribute.required_for_completeness'
    ) as HTMLInputElement;
    expect(requiredForCompletenessInput).toBeInTheDocument();
    expect(requiredForCompletenessInput).toHaveAttribute('readonly');
  });

  it('should render the component even if the attribute is not yet updated', () => {
    renderWithProviders(
        <ColumnDefinitionProperties
            selectedColumn={getSelectColumnDefinitionWithId()}
            onChange={jest.fn()}
            activeLocales={[getEnUsLocale()]}
            catalogLocaleCode={'en_US'}
            isDuplicateColumnCode={() => false}
            savedColumnIds={[]}
            attribute={getTableAttributeWithoutColumn()}
            columnDefinitionPropertiesMapping={columnDefinitionPropertiesMapping}
        />
    );

    expect(screen.getByText(/pim_table_attribute.form.attribute.column_code/)).toBeInTheDocument();
    expect(screen.getByText(/pim_table_attribute.form.attribute.data_type/)).toBeInTheDocument();
    expect(screen.getByText(/pim_table_attribute.form.attribute.labels/)).toBeInTheDocument();
  });

  it('should callback changes', () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <ColumnDefinitionProperties
        selectedColumn={getNumberColumnDefinitionWithId()}
        onChange={handleChange}
        activeLocales={[getEnUsLocale()]}
        catalogLocaleCode={'en_US'}
        isDuplicateColumnCode={() => false}
        savedColumnIds={[]}
        attribute={getComplexTableAttribute()}
        columnDefinitionPropertiesMapping={columnDefinitionPropertiesMapping}
      />
    );

    const codeInput = screen.getByLabelText(/pim_table_attribute.form.attribute.column_code/) as HTMLInputElement;
    fireEvent.change(codeInput, {target: {value: 'somethingelse'}});

    const englishInput = screen.getByLabelText(/English \(United States\)/) as HTMLInputElement;
    fireEvent.change(englishInput, {target: {value: 'Something Else'}});

    const minInput = screen.getByLabelText('pim_table_attribute.validations.min') as HTMLInputElement;
    fireEvent.change(minInput, {target: {value: '10'}});

    const requiredForCompletenessInput = screen.getByLabelText(
      'pim_table_attribute.form.attribute.required_for_completeness'
    ) as HTMLInputElement;
    fireEvent.click(requiredForCompletenessInput);

    expect(handleChange).toBeCalledWith({
      ...getNumberColumnDefinitionWithId(),
      code: 'somethingelse',
      labels: {en_US: 'Something Else'},
      validations: {
        min: 10,
      },
      is_required_for_completeness: false,
    });
  });

  it('should display violation on when code is empty', () => {
    renderWithProviders(
      <ColumnDefinitionProperties
        selectedColumn={{...getNumberColumnDefinitionWithId(), code: ''}}
        onChange={jest.fn()}
        activeLocales={[getEnUsLocale()]}
        catalogLocaleCode={'en_US'}
        isDuplicateColumnCode={() => false}
        savedColumnIds={[]}
        attribute={getComplexTableAttribute()}
        columnDefinitionPropertiesMapping={columnDefinitionPropertiesMapping}
      />
    );

    expect(screen.getByText('pim_table_attribute.validations.column_code_must_be_filled')).toBeInTheDocument();
  });

  it('should display violation on when code is invalid', () => {
    renderWithProviders(
      <ColumnDefinitionProperties
        selectedColumn={{...getNumberColumnDefinitionWithId(), code: '&&'}}
        onChange={jest.fn()}
        activeLocales={[getEnUsLocale()]}
        catalogLocaleCode={'en_US'}
        isDuplicateColumnCode={() => false}
        savedColumnIds={[]}
        attribute={getComplexTableAttribute()}
        columnDefinitionPropertiesMapping={columnDefinitionPropertiesMapping}
      />
    );

    expect(screen.getByText('pim_table_attribute.validations.invalid_column_code')).toBeInTheDocument();
  });

  it('should display violation on when code is duplicate', () => {
    renderWithProviders(
      <ColumnDefinitionProperties
        selectedColumn={getNumberColumnDefinitionWithId()}
        onChange={jest.fn()}
        activeLocales={[getEnUsLocale()]}
        catalogLocaleCode={'en_US'}
        isDuplicateColumnCode={() => true}
        savedColumnIds={[]}
        attribute={getComplexTableAttribute()}
        columnDefinitionPropertiesMapping={columnDefinitionPropertiesMapping}
      />
    );

    expect(screen.getByText('pim_table_attribute.validations.duplicated_column_code')).toBeInTheDocument();
  });

  it('should display is required for completeness as an active checkbox', () => {
    renderWithProviders(
      <ColumnDefinitionProperties
        selectedColumn={getNumberColumnDefinitionWithId()}
        onChange={jest.fn()}
        activeLocales={[getEnUsLocale()]}
        catalogLocaleCode={'en_US'}
        isDuplicateColumnCode={() => true}
        savedColumnIds={[]}
        attribute={getComplexTableAttribute()}
        columnDefinitionPropertiesMapping={columnDefinitionPropertiesMapping}
      />
    );

    const requiredForCompletenessInput = screen.getByLabelText(
      'pim_table_attribute.form.attribute.required_for_completeness'
    ) as HTMLInputElement;
    expect(requiredForCompletenessInput).toBeInTheDocument();
    expect(requiredForCompletenessInput).not.toHaveAttribute('readonly');
  });
});
