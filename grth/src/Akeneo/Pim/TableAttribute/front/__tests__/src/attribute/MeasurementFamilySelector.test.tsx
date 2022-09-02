import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {screen, fireEvent} from '@testing-library/react';
import {MeasurementFamilySelector} from '../../../src/attribute/MeasurementFamilySelector';

jest.mock('../../../src/fetchers/MeasurementFamilyFetcher');

describe('MeasurementFamilySelector', () => {
  it('should display existing measurement family', async () => {
    renderWithProviders(<MeasurementFamilySelector value={'ElectricCharge'} onChange={jest.fn()} />);

    expect(await screen.findByText('Electric charge')).toBeInTheDocument();
  });

  it('should update measurement family', async () => {
    const handleChange = jest.fn();
    renderWithProviders(<MeasurementFamilySelector value={undefined} onChange={handleChange} />);

    fireEvent.click(await screen.findByTitle('pim_common.open'));
    expect(await screen.findByText('Electric charge')).toBeInTheDocument();
    expect(await screen.findByText('Energy')).toBeInTheDocument();
    expect(await screen.findByText('Force')).toBeInTheDocument();

    fireEvent.click(await screen.findByText('Force'));
    expect(handleChange).toBeCalledWith('Force');
  });

  it('should clear measurement family', async () => {
    const handleChange = jest.fn();
    renderWithProviders(<MeasurementFamilySelector value={'ElectricCharge'} onChange={handleChange} />);

    fireEvent.click(await screen.findByTitle('pim_common.clear_value'));
    expect(handleChange).toBeCalledWith(undefined);
  });
});
