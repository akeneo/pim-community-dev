import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {ColumnDefinitionProperties} from '../../../src/attribute';
import {fireEvent, screen} from '@testing-library/react';
import {getEnUsLocale} from '../factories/Locales';
import {
  columnDefinitionPropertiesMapping,
  getNumberColumnDefinitionWithId,
  getSelectColumnDefinitionWithId,
} from '../factories/ColumnDefinition';
import {getTableAttribute} from '../factories/Attributes';

jest.mock('../../../src/attribute/ManageOptionsModal');

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
        attribute={getTableAttribute()}
        columnDefinitionPropertiesMapping={columnDefinitionPropertiesMapping}
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
        attribute={getTableAttribute()}
        columnDefinitionPropertiesMapping={columnDefinitionPropertiesMapping}
      />
    );

    const codeInput = screen.getByLabelText(/pim_table_attribute.form.attribute.column_code/) as HTMLInputElement;
    fireEvent.change(codeInput, {target: {value: 'somethingelse'}});
    expect(handleChange).toBeCalledWith({...getSelectColumnDefinitionWithId(), code: 'somethingelse'});
  });

  it('should display violations on validation fields', () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <ColumnDefinitionProperties
        selectedColumn={{...getNumberColumnDefinitionWithId(), validations: {min: 50, max: 10}}}
        onChange={handleChange}
        activeLocales={[getEnUsLocale()]}
        catalogLocaleCode={'en_US'}
        isDuplicateColumnCode={() => false}
        savedColumnIds={[]}
        attribute={getTableAttribute()}
        columnDefinitionPropertiesMapping={columnDefinitionPropertiesMapping}
      />
    );

    expect(screen.getByText('pim_table_attribute.validations.max_greater_than_min')).toBeInTheDocument();
  });

  it('should display violation on when code is empty', () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <ColumnDefinitionProperties
        selectedColumn={{...getNumberColumnDefinitionWithId(), code: ''}}
        onChange={handleChange}
        activeLocales={[getEnUsLocale()]}
        catalogLocaleCode={'en_US'}
        isDuplicateColumnCode={() => false}
        savedColumnIds={[]}
        attribute={getTableAttribute()}
        columnDefinitionPropertiesMapping={columnDefinitionPropertiesMapping}
      />
    );

    expect(screen.getByText('pim_table_attribute.validations.column_code_must_be_filled')).toBeInTheDocument();
  });

  it('should display violation on when code is invalid', () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <ColumnDefinitionProperties
        selectedColumn={{...getNumberColumnDefinitionWithId(), code: '&&'}}
        onChange={handleChange}
        activeLocales={[getEnUsLocale()]}
        catalogLocaleCode={'en_US'}
        isDuplicateColumnCode={() => false}
        savedColumnIds={[]}
        attribute={getTableAttribute()}
        columnDefinitionPropertiesMapping={columnDefinitionPropertiesMapping}
      />
    );

    expect(screen.getByText('pim_table_attribute.validations.invalid_column_code')).toBeInTheDocument();
  });

  it('should display violation on when code is duplicate', () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <ColumnDefinitionProperties
        selectedColumn={getNumberColumnDefinitionWithId()}
        onChange={handleChange}
        activeLocales={[getEnUsLocale()]}
        catalogLocaleCode={'en_US'}
        isDuplicateColumnCode={() => true}
        savedColumnIds={[]}
        attribute={getTableAttribute()}
        columnDefinitionPropertiesMapping={columnDefinitionPropertiesMapping}
      />
    );

    expect(screen.getByText('pim_table_attribute.validations.duplicated_column_code')).toBeInTheDocument();
  });

  it('should save options', () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <ColumnDefinitionProperties
        selectedColumn={getSelectColumnDefinitionWithId()}
        onChange={handleChange}
        activeLocales={[getEnUsLocale()]}
        catalogLocaleCode={'en_US'}
        isDuplicateColumnCode={() => false}
        savedColumnIds={[]}
        attribute={getTableAttribute()}
        columnDefinitionPropertiesMapping={columnDefinitionPropertiesMapping}
      />
    );

    fireEvent.click(screen.getByText('pim_table_attribute.form.attribute.manage_options'));
    fireEvent.click(screen.getByText('Fake confirm'));

    expect(handleChange).toBeCalledWith({
      ...getSelectColumnDefinitionWithId(),
      options: [{code: 'fake_code', labels: {en_US: 'fake label '}}],
    });
  });
});
