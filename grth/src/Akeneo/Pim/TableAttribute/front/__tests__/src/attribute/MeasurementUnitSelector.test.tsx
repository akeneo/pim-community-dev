import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {fireEvent, screen} from '@testing-library/react';
import {MeasurementUnitSelector} from '../../../src/attribute/MeasurementUnitSelector';

jest.mock('../../../src/fetchers/MeasurementFamilyFetcher');

describe('MeasurementUnitSelector', () => {
  it('should display existing measurement unit', async () => {
    renderWithProviders(
      <MeasurementUnitSelector measurementFamilyCode={'Energy'} value={'JOULE'} onChange={jest.fn()} />
    );

    expect(await screen.findByText('joule')).toBeInTheDocument();
  });

  it('should update measurement unit', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <MeasurementUnitSelector measurementFamilyCode={'Energy'} value={undefined} onChange={handleChange} />
    );

    fireEvent.click(await screen.findByTitle('pim_common.open'));
    expect(await screen.findByText('joule')).toBeInTheDocument();
    expect(await screen.findByText('calorie')).toBeInTheDocument();
    expect(await screen.findByText('kilocalorie')).toBeInTheDocument();

    fireEvent.click(await screen.findByText('joule'));
    expect(handleChange).toBeCalledWith('JOULE');
  });

  it('should be readonly if there is no measurement family code', async () => {
    renderWithProviders(
      <MeasurementUnitSelector
        data-testid={'blabla'}
        measurementFamilyCode={undefined}
        value={'JOULE'}
        onChange={jest.fn()}
      />
    );

    expect(await screen.findByText('JOULE')).toBeInTheDocument();
    expect(screen.getByRole('textbox')).toBeDisabled();
  });
});
