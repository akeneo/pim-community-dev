import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {screen} from '@testing-library/react';
import {JobExecutionList} from './JobExecutionList';
import {JobExecutionSearchTableResult} from '../models/JobExecutionSearchTableResult';

jest.mock('@akeneo-pim-community/shared/lib/components/PimView', () => ({
  PimView: () => <></>,
}));

jest.mock('../hooks/useJobExecutionSearchTableResult', () => ({
  useJobExecutionSearchTableResult: (): JobExecutionSearchTableResult => ({
    items: [],
    matches_count: 0,
    total_count: 0,
  }),
}));

test('it renders breadcrumb', () => {
  renderWithProviders(<JobExecutionList />);

  expect(screen.getByText('pim_menu.tab.activity')).toBeInTheDocument();
  expect(screen.getByText('pim_menu.item.job_tracker')).toBeInTheDocument();
});

test('it renders matches job count in page title', () => {
  renderWithProviders(<JobExecutionList />);

  expect(screen.getByText('pim_enrich.entity.job_execution.page_title.index')).toBeInTheDocument();
});
