import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {fireEvent, screen} from '@testing-library/react';
import {getComplexTableAttribute} from '../../../factories';
import MeasurementProperties from '../../../../src/attribute/ColumDefinitionProperties/MeasurementProperties';

jest.mock('../../../../src/fetchers/MeasurementFamilyFetcher');

describe('MeasurementProperties', () => {
  it('should render the component', async () => {
    const handleChange = jest.fn();
    const tableAttribute = getComplexTableAttribute();
    renderWithProviders(
      <MeasurementProperties
        attribute={tableAttribute}
        selectedColumn={tableAttribute.table_configuration[5]}
        handleChange={handleChange}
      />
    );

    expect(screen.getByText('pim_table_attribute.form.attribute.measurement_family')).toBeInTheDocument();
    expect(await screen.findByText('Electric charge')).toBeInTheDocument();

    expect(screen.getByText('pim_table_attribute.form.attribute.measurement_default_unit')).toBeInTheDocument();
    expect(await screen.findByText('Milliampere hour')).toBeInTheDocument();

    fireEvent.click(screen.getByTitle('pim_common.open'));
    fireEvent.click(screen.getByText('Millicoulomb'));

    expect(handleChange).toBeCalledWith({
      ...tableAttribute.table_configuration[5],
      measurement_default_unit_code: 'MILLICOULOMB',
    });
  });
});
