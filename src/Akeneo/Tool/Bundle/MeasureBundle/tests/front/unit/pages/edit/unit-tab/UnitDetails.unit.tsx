import React from 'react';
import {act, fireEvent, screen, waitFor} from '@testing-library/react';
import {UnitDetails} from 'akeneomeasure/pages/edit/unit-tab/UnitDetails';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';

declare global {
  namespace NodeJS {
    interface Global {
      fetch: any;
    }
  }
}

const measurementFamily = {
  code: 'AREA',
  labels: {
    en_US: 'Area',
  },
  standard_unit_code: 'SQUARE_METER',
  units: [
    {
      code: 'SQUARE_METER',
      labels: {
        en_US: 'Square Meter',
      },
      symbol: 'sqm',
      convert_from_standard: [
        {
          operator: 'mul',
          value: '1',
        },
      ],
    },
    {
      code: 'SQUARE_FEET',
      labels: {
        en_US: 'Square feet',
      },
      symbol: 'sqm',
      convert_from_standard: [
        {
          operator: 'mul',
          value: '1',
        },
      ],
    },
  ],
  is_locked: false,
};

beforeAll(() => {
  const mockFetch = jest.fn().mockImplementationOnce(route => {
    switch (route) {
      case 'pim_localization_locale_index':
        return {
          json: () => [
            {
              code: 'en_US',
            },
            {
              code: 'fr_FR',
            },
          ],
        };
      default:
        return {json: () => []};
    }
  });

  global.fetch = mockFetch;
});

afterAll(() => {
  global.fetch && global.fetch.mockClear();
  delete global.fetch;
});

test('It displays a details edit form', async () => {
  let selectedUnitCode = 'SQUARE_METER';
  const onMeasurementFamilyChange = () => {};
  const selectUnitCode = newSelectedUnitCode => {
    selectedUnitCode = newSelectedUnitCode;
  };
  const errors = [];

  renderWithProviders(
    <UnitDetails
      measurementFamily={measurementFamily}
      selectedUnitCode={selectedUnitCode}
      onMeasurementFamilyChange={onMeasurementFamilyChange}
      selectUnitCode={selectUnitCode}
      errors={errors}
    />
  );

  await waitFor(() => {
    expect(screen.getByText('measurements.unit.title')).toBeInTheDocument();
    expect((screen.getByRole('unit-label-input-en_US') as HTMLInputElement).value).toEqual('Square Meter');
  });
});

test('It allows symbol edition', async () => {
  let selectedUnitCode = 'SQUARE_METER';
  let updatedMeasurementFamily = measurementFamily;
  const onMeasurementFamilyChange = newMeasurementFamily => {
    updatedMeasurementFamily = newMeasurementFamily;
  };
  const selectUnitCode = newSelectedUnitCode => {
    selectedUnitCode = newSelectedUnitCode;
  };
  const errors = [];

  renderWithProviders(
    <UnitDetails
      measurementFamily={measurementFamily}
      selectedUnitCode={selectedUnitCode}
      onMeasurementFamilyChange={onMeasurementFamilyChange}
      selectUnitCode={selectUnitCode}
      errors={errors}
    />
  );

  await act(async () => {
    const symbolInput = screen.getByRole('unit-symbol-input') as HTMLInputElement;
    fireEvent.change(symbolInput, {target: {value: 'm^2'}});
  });

  expect(updatedMeasurementFamily.units[0].symbol).toEqual('m^2');
});

test('It allows convertion value edition', async () => {
  let selectedUnitCode = 'SQUARE_METER';
  let updatedMeasurementFamily = measurementFamily;
  const onMeasurementFamilyChange = newMeasurementFamily => {
    updatedMeasurementFamily = newMeasurementFamily;
  };
  const selectUnitCode = newSelectedUnitCode => {
    selectedUnitCode = newSelectedUnitCode;
  };
  const errors = [];

  renderWithProviders(
    <UnitDetails
      measurementFamily={measurementFamily}
      selectedUnitCode={selectedUnitCode}
      onMeasurementFamilyChange={onMeasurementFamilyChange}
      selectUnitCode={selectUnitCode}
      errors={errors}
    />
  );

  await act(async () => {
    const operationValueInput = screen.getByRole('operation-value-input') as HTMLInputElement;
    fireEvent.change(operationValueInput, {target: {value: '2'}});
  });

  expect(updatedMeasurementFamily.units[0].convert_from_standard[0].value).toEqual('2');
});

test('It allows label edition', async () => {
  let selectedUnitCode = 'SQUARE_METER';
  let updatedMeasurementFamily = measurementFamily;
  const onMeasurementFamilyChange = newMeasurementFamily => {
    updatedMeasurementFamily = newMeasurementFamily;
  };
  const selectUnitCode = newSelectedUnitCode => {
    selectedUnitCode = newSelectedUnitCode;
  };
  const errors = [];

  renderWithProviders(
    <UnitDetails
      measurementFamily={measurementFamily}
      selectedUnitCode={selectedUnitCode}
      onMeasurementFamilyChange={onMeasurementFamilyChange}
      selectUnitCode={selectUnitCode}
      errors={errors}
    />
  );

  await waitFor(() => screen.getByRole('unit-label-input-en_US'));

  act(() => {
    const labelInput = screen.getByRole('unit-label-input-en_US') as HTMLInputElement;
    fireEvent.change(labelInput, {target: {value: 'square meter'}});
  });

  await waitFor(() => {
    expect(updatedMeasurementFamily.units[0].labels['en_US']).toEqual('square meter');
  });
});

test('It allows to delete the unit', async () => {
  let selectedUnitCode = 'SQUARE_FEET';
  let updatedMeasurementFamily = measurementFamily;
  const onMeasurementFamilyChange = newMeasurementFamily => {
    updatedMeasurementFamily = newMeasurementFamily;
  };
  const selectUnitCode = newSelectedUnitCode => {
    selectedUnitCode = newSelectedUnitCode;
  };
  const errors = [];

  renderWithProviders(
    <UnitDetails
      measurementFamily={measurementFamily}
      selectedUnitCode={selectedUnitCode}
      onMeasurementFamilyChange={onMeasurementFamilyChange}
      selectUnitCode={selectUnitCode}
      errors={errors}
    />
  );

  const deleteButton = screen.getByText('measurements.unit.delete.button');
  fireEvent.click(deleteButton);

  const confirmButton = await waitFor(() => screen.getByText('pim_common.delete'));
  fireEvent.click(confirmButton);

  expect(updatedMeasurementFamily.units.length).toEqual(1);
});

test('It does not render if the selected unit is not found', async () => {
  const selectedUnitCode = 'NOT_FOUND';

  renderWithProviders(
    <UnitDetails
      measurementFamily={measurementFamily}
      selectedUnitCode={selectedUnitCode}
      onMeasurementFamilyChange={jest.fn()}
      selectUnitCode={jest.fn()}
      errors={[]}
    />
  );

  await waitFor(() => {
    expect(screen.queryByText('measurements.unit.title')).not.toBeInTheDocument();
  });
});
