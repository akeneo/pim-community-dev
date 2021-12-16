import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {screen} from '@testing-library/react';
import SelectProperties from '../../../../src/attribute/ColumDefinitionProperties/SelectProperties';
import {getComplexTableAttribute} from '../../../factories';
import {SelectColumnDefinition} from '../../../../src';
import {fireEvent} from '@testing-library/dom';

jest.mock('../../../../src/attribute/ManageOptionsModal');

const selectedColumn = getComplexTableAttribute().table_configuration.find(
  columnDefinition => columnDefinition.data_type === 'select'
) as SelectColumnDefinition;

describe('SelectProperties', () => {
  it('should render the component', () => {
    renderWithProviders(
      <SelectProperties
        attribute={getComplexTableAttribute()}
        selectedColumn={selectedColumn}
        handleChange={jest.fn()}
      />
    );

    expect(screen.getByText('pim_table_attribute.form.attribute.manage_options')).toBeInTheDocument();
  });

  it('should callback changes', () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <SelectProperties
        attribute={getComplexTableAttribute()}
        selectedColumn={selectedColumn}
        handleChange={handleChange}
      />
    );

    fireEvent.click(screen.getByText('pim_table_attribute.form.attribute.manage_options'));
    fireEvent.click(screen.getByText('Fake confirm'));

    expect(handleChange).toBeCalledWith({
      ...selectedColumn,
      options: [{code: 'fake_code', labels: {en_US: 'fake label'}}],
    });
  });
});
