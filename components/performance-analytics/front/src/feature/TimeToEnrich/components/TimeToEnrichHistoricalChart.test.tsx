import React from 'react';
import {renderWithProviders} from '../../tests';
import {screen} from '@testing-library/react';
import {TimeToEnrichHistoricalChart} from './TimeToEnrichHistoricalChart';

test('it renders a historical chart for TTE', async () => {
  const data = [
    {
      code: '2021-W45',
      value: 10,
    },
    {
      code: '2021-W46',
      value: 20,
    },
  ];

  await renderWithProviders(<TimeToEnrichHistoricalChart referenceTimeToEnrichList={data} />);

  expect(screen.getByText('2021-W45')).toBeInTheDocument();
  expect(screen.getByText('2021-W46')).toBeInTheDocument();
  expect(screen.getAllByText('10')).toHaveLength(3);
  expect(screen.getAllByText('20')).toHaveLength(3);
});

test('it resizes the chart', async () => {
  const data = [
    {
      code: '2021-W45',
      value: 10,
    },
    {
      code: '2021-W46',
      value: 20,
    },
  ];

  await renderWithProviders(<TimeToEnrichHistoricalChart referenceTimeToEnrichList={data} />);

  expect(screen.getByText('2021-W45')).toBeInTheDocument();
  expect(screen.getByText('2021-W46')).toBeInTheDocument();
  expect(screen.getAllByText('10')).toHaveLength(3);
  expect(screen.getAllByText('20')).toHaveLength(3);

  global.innerWidth = 500;
  global.dispatchEvent(new Event('resize'));
  await new Promise(r => setTimeout(r, 200));

  expect(screen.getByText('2021-W45')).toBeInTheDocument();
  expect(screen.getByText('2021-W46')).toBeInTheDocument();
  expect(screen.getAllByText('10')).toHaveLength(3);
  expect(screen.getAllByText('20')).toHaveLength(3);
});
