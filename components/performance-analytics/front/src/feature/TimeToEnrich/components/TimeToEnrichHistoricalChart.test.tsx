import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
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

  renderWithProviders(<TimeToEnrichHistoricalChart referenceTimeToEnrichList={data} />);

  expect(screen.getByText('2021-W45')).toBeInTheDocument();
  expect(screen.getByText('2021-W46')).toBeInTheDocument();
  expect(screen.getByText('10')).toBeInTheDocument();
  expect(screen.getByText('20')).toBeInTheDocument();
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

  renderWithProviders(<TimeToEnrichHistoricalChart referenceTimeToEnrichList={data} />);

  expect(screen.getByText('2021-W45')).toBeInTheDocument();
  expect(screen.getByText('2021-W46')).toBeInTheDocument();
  expect(screen.getByText('10')).toBeInTheDocument();
  expect(screen.getByText('20')).toBeInTheDocument();

  global.innerWidth = 500;
  global.dispatchEvent(new Event('resize'));
  await new Promise(r => setTimeout(r, 200));

  expect(screen.getByText('2021-W45')).toBeInTheDocument();
  expect(screen.getByText('2021-W46')).toBeInTheDocument();
  expect(screen.getByText('10')).toBeInTheDocument();
  expect(screen.getByText('20')).toBeInTheDocument();
});
