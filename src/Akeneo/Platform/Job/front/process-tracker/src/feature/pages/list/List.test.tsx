import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {screen} from '@testing-library/react';
import {List} from './List';
import {JobSearchResult} from '../../models/JobSearchResult';

jest.mock('@akeneo-pim-community/shared/lib/components/PimView', () => ({
  PimView: () => <></>,
}));

jest.mock('../../hooks/useJobSearchResult', () => ({
  useJobSearchResult: (): JobSearchResult => ({
    items: [],
    matches_count: 0,
    total_count: 0,
  }),
}));

test('it renders breadcrumb', () => {
  renderWithProviders(<List />);

  expect(screen.getByText('pim_menu.tab.activity')).toBeInTheDocument();
  expect(screen.getByText('pim_menu.item.job_tracker')).toBeInTheDocument();
});

test('it renders matches job count in page title', () => {
  renderWithProviders(<List />);

  expect(screen.getByText('pim_enrich.entity.job_execution.page_title.index')).toBeInTheDocument();
});
