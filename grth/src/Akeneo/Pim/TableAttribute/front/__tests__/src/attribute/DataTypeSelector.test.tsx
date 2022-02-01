import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {fireEvent, screen} from '@testing-library/react';
import {DataTypeSelector} from '../../../src/attribute/DataTypeSelector';
import {renderWithFeatureFlag} from '../../shared/renderWithFeatureFlag';

jest.mock('../../../src/fetchers/LocaleFetcher');

describe('DataTypeSelector', () => {
  it('should select a data type', () => {
    const handleChange = jest.fn();
    renderWithFeatureFlag(<DataTypeSelector dataType={null} isFirstColumn={false} onChange={handleChange} />, {
      reference_entity: false,
    });

    fireEvent.click(screen.getByTitle('pim_common.open'));
    ['number', 'text', 'boolean', 'select'].forEach(dataType => {
      expect(screen.getByText(`pim_table_attribute.properties.data_type.${dataType}`)).toBeInTheDocument();
    });
    expect(screen.queryByText('pim_table_attribute.properties.data_type.reference_entity')).not.toBeInTheDocument();
    fireEvent.click(screen.getByText('pim_table_attribute.properties.data_type.boolean'));
    expect(handleChange).toBeCalledWith('boolean');
  });

  it('should display display reference entity data type', () => {
    renderWithFeatureFlag(<DataTypeSelector dataType={null} isFirstColumn={false} onChange={jest.fn()} />, {
      reference_entity: true,
    });

    fireEvent.click(screen.getByTitle('pim_common.open'));
    expect(screen.getByText('pim_table_attribute.properties.data_type.reference_entity')).toBeInTheDocument();
  });

  it('should only display first column data types', async () => {
    renderWithFeatureFlag(<DataTypeSelector dataType={null} isFirstColumn={true} onChange={jest.fn()} />, {
      reference_entity: true,
    });

    fireEvent.click(screen.getByTitle('pim_common.open'));
    expect(screen.getByText('pim_table_attribute.properties.data_type.select')).toBeInTheDocument();
    expect(screen.getByText('pim_table_attribute.properties.data_type.reference_entity')).toBeInTheDocument();
    expect(screen.queryByText('pim_table_attribute.properties.data_type.boolean')).not.toBeInTheDocument();
  });

  it('should only display selected data type', () => {
    renderWithProviders(<DataTypeSelector dataType={'boolean'} isFirstColumn={false} onChange={jest.fn()} />);

    expect(screen.getByText('pim_table_attribute.properties.data_type.boolean')).toBeInTheDocument();
  });
});
