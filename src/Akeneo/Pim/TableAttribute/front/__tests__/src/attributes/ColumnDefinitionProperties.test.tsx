import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {ColumnDefinitionProperties} from '../../../src/attribute/ColumnDefinitionProperties';
import {screen, fireEvent} from '@testing-library/react';
import {getEnUsLocale} from '../factories/Locales';
import {
  getNumberColumnDefinitionWithId,
  getSelectColumnDefinitionWithId,
  getTextColumnDefinitionWithId,
} from '../factories/ColumnDefinition';

describe('ColumnDefinitionProperties', () => {
  it('should render the component', () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <ColumnDefinitionProperties
        selectedColumn={getSelectColumnDefinitionWithId()}
        onChange={handleChange}
        activeLocales={[getEnUsLocale()]}
        catalogLocaleCode={'en_US'}
        isDuplicateColumnCode={() => false}
        savedColumnIds={[]}
      />
    );
  });

  it('should update the code', () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <ColumnDefinitionProperties
        selectedColumn={getSelectColumnDefinitionWithId()}
        onChange={handleChange}
        activeLocales={[getEnUsLocale()]}
        catalogLocaleCode={'en_US'}
        isDuplicateColumnCode={() => false}
        savedColumnIds={[]}
      />
    );

    const codeInput = screen.getByLabelText('pim_common.code') as HTMLInputElement;
    fireEvent.change(codeInput, {target: {value: 'somethingelse'}});
    expect(handleChange).toBeCalledWith({
      code: 'somethingelse',
      validations: {},
      data_type: 'select',
      labels: {},
      id: 'uniqueidingredient',
    });
  });

  it('should update the max length', () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <ColumnDefinitionProperties
        selectedColumn={getTextColumnDefinitionWithId()}
        onChange={handleChange}
        activeLocales={[getEnUsLocale()]}
        catalogLocaleCode={'en_US'}
        isDuplicateColumnCode={() => false}
        savedColumnIds={[]}
      />
    );

    const maxLengthInput = screen.getByLabelText('pim_table_attribute.validations.max_length') as HTMLInputElement;
    fireEvent.change(maxLengthInput, {target: {value: '10'}});
    expect(handleChange).toBeCalledWith({
      code: 'part',
      validations: {
        max_length: 10,
      },
      data_type: 'text',
      labels: {},
      id: 'uniqueidpart',
    });
  });

  it('should display validation errors', () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <ColumnDefinitionProperties
        selectedColumn={{...getNumberColumnDefinitionWithId(), validations: {min: 50, max: 10}}}
        onChange={handleChange}
        activeLocales={[getEnUsLocale()]}
        catalogLocaleCode={'en_US'}
        isDuplicateColumnCode={() => false}
        savedColumnIds={[]}
      />
    );

    expect(screen.getByText('pim_table_attribute.validations.max_greater_than_min')).toBeInTheDocument();
  });
});
