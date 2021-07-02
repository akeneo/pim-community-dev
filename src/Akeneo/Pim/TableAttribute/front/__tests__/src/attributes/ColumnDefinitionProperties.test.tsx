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
      />
    );

    const codeInput = screen.getByLabelText('pim_common.code') as HTMLInputElement;
    fireEvent.change(codeInput, {target: {value: 'somethingelse'}});
    expect(handleChange).toBeCalledWith({...getSelectColumnDefinitionWithId(), code: 'somethingelse'});
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
        attribute={getTableAttribute()}
      />
    );

    const maxLengthInput = screen.getByLabelText('pim_table_attribute.validations.max_length') as HTMLInputElement;
    fireEvent.change(maxLengthInput, {target: {value: '10'}});
    expect(handleChange).toBeCalledWith({...getTextColumnDefinitionWithId(), validations: {max_length: 10}});
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
        attribute={getTableAttribute()}
      />
    );

    expect(screen.getByText('pim_table_attribute.validations.max_greater_than_min')).toBeInTheDocument();
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
