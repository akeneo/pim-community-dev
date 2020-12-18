import React from 'react';
import {Router} from 'react-router';
import {screen, fireEvent} from '@testing-library/react';
import {MeasurementFamilyTable} from 'akeneomeasure/pages/list/MeasurementFamilyTable';
import {createMemoryHistory} from 'history';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';

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

test('It displays an empty table', () => {
  const history = createMemoryHistory();

  renderWithProviders(
    <Router history={history}>
      <MeasurementFamilyTable
        measurementFamilies={[]}
        toggleSortDirection={() => 'descending'}
        getSortDirection={() => 'descending'}
      />
    </Router>
  );

  expect(screen.getByText('pim_common.label')).toBeInTheDocument();
  expect(screen.queryByRole('cell')).not.toBeInTheDocument();
});

test('It displays some measurement families', () => {
  const history = createMemoryHistory();

  renderWithProviders(
    <Router history={history}>
      <MeasurementFamilyTable
        measurementFamilies={measurementFamilies}
        toggleSortDirection={() => 'descending'}
        getSortDirection={() => 'descending'}
      />
    </Router>
  );

  expect(screen.getAllByRole('row')).toHaveLength(3); // 1 header row + 2 rows
  expect(screen.getByText('AREA')).toBeInTheDocument();
  expect(screen.getByText('LENGTH')).toBeInTheDocument();
});

test('It toggles the sort direction on the columns', () => {
  const history = createMemoryHistory();
  let sortDirections = {
    label: 'ascending',
    code: 'ascending',
    standard_unit: 'ascending',
    unit_count: 'ascending',
  };

  renderWithProviders(
    <Router history={history}>
      <MeasurementFamilyTable
        measurementFamilies={measurementFamilies}
        toggleSortDirection={(columnCode: string) => (sortDirections[columnCode] = 'descending')}
        getSortDirection={(columnCode: string) => sortDirections[columnCode]}
      />
    </Router>
  );

  fireEvent.click(screen.getByText('pim_common.label'));
  fireEvent.click(screen.getByText('pim_common.code'));
  fireEvent.click(screen.getByText('measurements.list.header.standard_unit'));
  fireEvent.click(screen.getByText('measurements.list.header.unit_count'));

  expect(Object.values(sortDirections).every(direction => direction === 'descending')).toBe(true);
});

test('It changes the history when clicking on a row', () => {
  const history = createMemoryHistory();

  renderWithProviders(
    <Router history={history}>
      <MeasurementFamilyTable
        measurementFamilies={measurementFamilies}
        toggleSortDirection={() => 'descending'}
        getSortDirection={() => 'descending'}
      />
    </Router>
  );

  fireEvent.click(screen.getByText('Area'));

  expect(history.location.pathname).toEqual('/AREA');
});
