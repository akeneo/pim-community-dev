import React from 'react';
import ReactDOM from 'react-dom';
import {act, fireEvent} from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';
import {AkeneoThemeProvider} from 'akeneomeasure/AkeneoThemeProvider';
import {Table} from 'akeneomeasure/pages/list/Table';

const dependencies = {
  legacy: {},
  translate: (key: string) => key,
  user: () => 'en_US',
  router: {},
  notify: () => {},
};

const measurementFamilies = [
  {
    code: 'AREA',
    labels: {
      en_US: 'Area',
    },
    standard_unit_code: 'SQUARE_FEET',
    units: [
      {
        code: 'SQUARE_METER',
        labels: {
          en_US: 'Square Meter',
        },
      },
    ],
  },
  {
    code: 'LENGTH',
    labels: {
      en_US: 'Length',
    },
    standard_unit_code: 'METER',
    units: [
      {
        code: 'METER',
        labels: {
          en_US: 'Meter',
        },
      },
    ],
  },
];

let container;
beforeEach(() => {
  container = document.createElement('div');
  document.body.appendChild(container);
});
afterEach(() => {
  document.body.removeChild(container);
  container = null;
});

test('It displays an empty table', async () => {
  await act(async () => {
    ReactDOM.render(
      <AkeneoThemeProvider>
        <Table measurementFamilies={[]} toggleSortDirection={() => {}} getSortDirection={() => {}} />
      </AkeneoThemeProvider>,
      container
    );
  });

  expect(container.querySelector('table')).toBeInTheDocument();
  expect(container.querySelector('tbody tr')).not.toBeInTheDocument();
});

test('It displays some measurement families', async () => {
  await act(async () => {
    ReactDOM.render(
      <AkeneoThemeProvider>
        <Table measurementFamilies={measurementFamilies} toggleSortDirection={() => {}} getSortDirection={() => {}} />
      </AkeneoThemeProvider>,
      container
    );
  });

  expect(container.querySelectorAll('tbody tr').length).toEqual(2);
});

test('It toggles the sort direction on the columns', async () => {
  let sortDirections = {
    label: 'Ascending',
    code: 'Ascending',
    standard_unit: 'Ascending',
    unit_count: 'Ascending',
  };

  await act(async () => {
    ReactDOM.render(
      <AkeneoThemeProvider>
        <Table
          measurementFamilies={measurementFamilies}
          toggleSortDirection={(columnCode: string) => (sortDirections[columnCode] = 'Descending')}
          getSortDirection={(columnCode: string) => sortDirections[columnCode]}
        />
      </AkeneoThemeProvider>,
      container
    );
  });

  const labelCell = container.querySelector('th[title="measurements.list.header.label"]');
  const codeCell = container.querySelector('th[title="measurements.list.header.code"]');
  const standardUnitCell = container.querySelector('th[title="measurements.list.header.standard_unit"]');
  const unitCountCell = container.querySelector('th[title="measurements.list.header.unit_count"]');

  await act(async () => {
    fireEvent.click(labelCell);
    fireEvent.click(codeCell);
    fireEvent.click(standardUnitCell);
    fireEvent.click(unitCountCell);
  });

  expect(Object.values(sortDirections).every(direction => direction === 'Descending')).toBe(true);
});
