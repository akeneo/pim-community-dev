import React from 'react';
import ReactDOM from 'react-dom';
import {Router} from 'react-router';
import {act, getByRole, getByText, fireEvent} from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';
import {AkeneoThemeProvider} from 'akeneomeasure/AkeneoThemeProvider';
import {UnitDetails} from 'akeneomeasure/pages/edit/unit-tab/UnitDetails';

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

let container;
beforeEach(() => {
  container = document.createElement('div');
  document.body.appendChild(container);
  const mockFetch = jest.fn().mockImplementationOnce(() => ({
    json: () => [
      {
        code: 'en_US',
      },
    ],
  }));

  global.fetch = mockFetch;
});
afterEach(() => {
  document.body.removeChild(container);
  container = null;
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
  await act(async () => {
    ReactDOM.render(
      <AkeneoThemeProvider>
        <UnitDetails
          measurementFamily={measurementFamily}
          selectedUnitCode={selectedUnitCode}
          onMeasurementFamilyChange={onMeasurementFamilyChange}
          selectUnitCode={selectUnitCode}
          errors={errors}
        />
      </AkeneoThemeProvider>,
      container
    );
  });

  expect(getByText(container, 'measurements.unit.title?unitLabel=%5BSQUARE_METER%5D')).toBeInTheDocument();
  expect((getByRole(container, 'unit-label-input-en_US') as HTMLInputElement).value).toEqual('Square Meter');
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
  await act(async () => {
    ReactDOM.render(
      <AkeneoThemeProvider>
        <UnitDetails
          measurementFamily={measurementFamily}
          selectedUnitCode={selectedUnitCode}
          onMeasurementFamilyChange={onMeasurementFamilyChange}
          selectUnitCode={selectUnitCode}
          errors={errors}
        />
      </AkeneoThemeProvider>,
      container
    );
  });

  act(() => {
    const symbolInput = getByRole(container, 'unit-symbol-input') as HTMLInputElement;
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
  await act(async () => {
    ReactDOM.render(
      <AkeneoThemeProvider>
        <UnitDetails
          measurementFamily={measurementFamily}
          selectedUnitCode={selectedUnitCode}
          onMeasurementFamilyChange={onMeasurementFamilyChange}
          selectUnitCode={selectUnitCode}
          errors={errors}
        />
      </AkeneoThemeProvider>,
      container
    );
  });

  act(() => {
    const operationValueInput = getByRole(container, 'operation-value-input') as HTMLInputElement;
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
  await act(async () => {
    ReactDOM.render(
      <AkeneoThemeProvider>
        <UnitDetails
          measurementFamily={measurementFamily}
          selectedUnitCode={selectedUnitCode}
          onMeasurementFamilyChange={onMeasurementFamilyChange}
          selectUnitCode={selectUnitCode}
          errors={errors}
        />
      </AkeneoThemeProvider>,
      container
    );
  });

  act(() => {
    const labelInput = getByRole(container, 'unit-label-input-en_US') as HTMLInputElement;
    fireEvent.change(labelInput, {target: {value: 'square meter'}});
  });

  expect(updatedMeasurementFamily.units[0].labels['en_US']).toEqual('square meter');
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
  await act(async () => {
    ReactDOM.render(
      <AkeneoThemeProvider>
        <UnitDetails
          measurementFamily={measurementFamily}
          selectedUnitCode={selectedUnitCode}
          onMeasurementFamilyChange={onMeasurementFamilyChange}
          selectUnitCode={selectUnitCode}
          errors={errors}
        />
      </AkeneoThemeProvider>,
      container
    );
  });

  act(() => {
    const deleteButton = getByText(container, 'measurements.unit.delete.button');
    fireEvent.click(deleteButton);
  });
  act(() => {
    const confirmButton = getByText(container, 'pim_common.delete');
    fireEvent.click(confirmButton);
  });

  expect(updatedMeasurementFamily.units.length).toEqual(1);
});

test('It does not render if the selected unit is not found', async () => {
  let selectedUnitCode = 'NOT_FOUND';
  let updatedMeasurementFamily = measurementFamily;
  const onMeasurementFamilyChange = newMeasurementFamily => {
    updatedMeasurementFamily = newMeasurementFamily;
  };
  const selectUnitCode = newSelectedUnitCode => {
    selectedUnitCode = newSelectedUnitCode;
  };
  const errors = [];
  await act(async () => {
    ReactDOM.render(
      <AkeneoThemeProvider>
        <UnitDetails
          measurementFamily={measurementFamily}
          selectedUnitCode={selectedUnitCode}
          onMeasurementFamilyChange={onMeasurementFamilyChange}
          selectUnitCode={selectUnitCode}
          errors={errors}
        />
      </AkeneoThemeProvider>,
      container
    );
  });

  expect(container.children.length).toBe(0);
});

// test('It displays some measurement families', async () => {
//   const history = createMemoryHistory();

//   await act(async () => {
//     ReactDOM.render(
//       <Router history={history}>
//         <AkeneoThemeProvider>
//           <MeasurementFamilyTable
//             measurementFamilies={measurementFamilies}
//             toggleSortDirection={() => {}}
//             getSortDirection={() => {}}
//           />
//         </AkeneoThemeProvider>
//       </Router>,
//       container
//     );
//   });

//   expect(container.querySelectorAll('tbody tr').length).toEqual(2);
// });

// test('It toggles the sort direction on the columns', async () => {
//   const history = createMemoryHistory();
//   let sortDirections = {
//     label: 'Ascending',
//     code: 'Ascending',
//     standard_unit: 'Ascending',
//     unit_count: 'Ascending',
//   };

//   await act(async () => {
//     ReactDOM.render(
//       <Router history={history}>
//         <AkeneoThemeProvider>
//           <MeasurementFamilyTable
//             measurementFamilies={measurementFamilies}
//             toggleSortDirection={(columnCode: string) => (sortDirections[columnCode] = 'Descending')}
//             getSortDirection={(columnCode: string) => sortDirections[columnCode]}
//           />
//         </AkeneoThemeProvider>
//       </Router>,
//       container
//     );
//   });

//   const labelCell = container.querySelector('th[title="pim_common.label"]');
//   const codeCell = container.querySelector('th[title="pim_common.code"]');
//   const standardUnitCell = container.querySelector('th[title="measurements.list.header.standard_unit"]');
//   const unitCountCell = container.querySelector('th[title="measurements.list.header.unit_count"]');

//   await act(async () => {
//     fireEvent.click(labelCell);
//     fireEvent.click(codeCell);
//     fireEvent.click(standardUnitCell);
//     fireEvent.click(unitCountCell);
//   });

//   expect(Object.values(sortDirections).every(direction => direction === 'Descending')).toBe(true);
// });

// test('It changes the history when clicking on a row', async () => {
//   const history = createMemoryHistory();

//   await act(async () => {
//     ReactDOM.render(
//       <Router history={history}>
//         <AkeneoThemeProvider>
//           <MeasurementFamilyTable
//             measurementFamilies={measurementFamilies}
//             toggleSortDirection={() => {}}
//             getSortDirection={() => {}}
//           />
//         </AkeneoThemeProvider>
//       </Router>,
//       container
//     );
//   });

//   const areaRow = container.querySelector('tbody tr[title="[AREA]"]');

//   await act(async () => {
//     fireEvent.click(areaRow);
//   });

//   expect(history.location.pathname).toEqual('/AREA');
// });
