import React from 'react';
import {screen, within} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {MultiCategoryTreeSelector} from './MultiCategoryTreeSelector';

const categories = [
  {
    id: 0,
    code: 'webcam',
    parent: null,
    labels: {en_US: 'Webcam'},
    selectedCategoryCount: 2,
  },
  {
    id: 1,
    code: 'scanners',
    parent: null,
    labels: {en_US: 'Scanners'},
  },
];

jest.mock('../../hooks/useCategoryTrees', () => ({
  useCategoryTrees: () => categories,
}));

test('it displays a Tab bar with the provided categories labelled & ordered and with the selected count if present', () => {
  const onCategorySelection = jest.fn();
  const categorySelection = ['webcam', 'scanners'];

  renderWithProviders(
    <MultiCategoryTreeSelector categorySelection={categorySelection} onCategorySelection={onCategorySelection} />
  );

  const tabs = screen.getAllByRole('tab');
  const scannersTab = screen.getByText('Scanners');
  const webcamTab = screen.getByText('Webcam');

  expect(scannersTab).toBe(tabs[0]);
  expect(webcamTab).toBe(tabs[1]);
  expect(within(webcamTab).getByText('2')).toBeInTheDocument();
});

test('it selects a Tab when clicking on it', () => {
  const onCategorySelection = jest.fn();
  const categorySelection = ['webcam', 'scanners'];

  renderWithProviders(
    <MultiCategoryTreeSelector categorySelection={categorySelection} onCategorySelection={onCategorySelection} />
  );

  const scannersTab = screen.getByText('Scanners');

  userEvent.click(scannersTab);

  expect(scannersTab).toHaveAttribute('aria-selected', 'true');
});
