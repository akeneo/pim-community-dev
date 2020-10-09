import React from 'react';
import {Router} from 'react-router';
import {act, fireEvent, getByTitle} from '@testing-library/react';
import {MeasurementFamilyTable} from 'akeneomeasure/pages/list/MeasurementFamilyTable';
import {createMemoryHistory} from 'history';
import {renderDOMWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';

const measurementFamilies = [
  {
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
      },
    ],
    is_locked: false,
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
    is_locked: false,
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
  const history = createMemoryHistory();

  await act(async () => {
    renderDOMWithProviders(
      <Router history={history}>
        <MeasurementFamilyTable measurementFamilies={[]} toggleSortDirection={() => {}} getSortDirection={() => {}} />
      </Router>,
      container
    );
  });

  expect(container.querySelector('table')).toBeInTheDocument();
  expect(container.querySelector('tbody tr')).not.toBeInTheDocument();
});

test('It displays some measurement families', async () => {
  const history = createMemoryHistory();

  await act(async () => {
    renderDOMWithProviders(
      <Router history={history}>
        <MeasurementFamilyTable
          measurementFamilies={measurementFamilies}
          toggleSortDirection={() => {}}
          getSortDirection={() => {}}
        />
      </Router>,
      container
    );
  });

  expect(container.querySelectorAll('tbody tr').length).toEqual(2);
});

test('It toggles the sort direction on the columns', async () => {
  const history = createMemoryHistory();
  let sortDirections = {
    label: 'Ascending',
    code: 'Ascending',
    standard_unit: 'Ascending',
    unit_count: 'Ascending',
  };

  await act(async () => {
    renderDOMWithProviders(
      <Router history={history}>
        <MeasurementFamilyTable
          measurementFamilies={measurementFamilies}
          toggleSortDirection={(columnCode: string) => (sortDirections[columnCode] = 'Descending')}
          getSortDirection={(columnCode: string) => sortDirections[columnCode]}
        />
      </Router>,
      container
    );
  });

  const labelCell = container.querySelector('th[title="pim_common.label"]');
  const codeCell = container.querySelector('th[title="pim_common.code"]');
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

test('It changes the history when clicking on a row', async () => {
  const history = createMemoryHistory();

  await act(async () => {
    renderDOMWithProviders(
      <Router history={history}>
        <MeasurementFamilyTable
          measurementFamilies={measurementFamilies}
          toggleSortDirection={() => {}}
          getSortDirection={() => {}}
        />
      </Router>,
      container
    );
  });

  const areaRow = getByTitle(container, 'Area');

  await act(async () => {
    fireEvent.click(areaRow);
  });

  expect(history.location.pathname).toEqual('/AREA');
});
