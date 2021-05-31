import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {AddSourceDropdown} from './AddSourceDropdown';
import userEvent from '@testing-library/user-event';
import {AvailableSourceGroup} from '../../../models';
import {act} from 'react-dom/test-utils';

global.beforeEach(() => {
  const intersectionObserverMock = () => ({
    observe: jest.fn(),
    unobserve: jest.fn(),
  });

  window.IntersectionObserver = jest.fn().mockImplementation(intersectionObserverMock);
});

jest.mock('../../../hooks/useAvailableSourcesFetcher', () => ({
  useAvailableSourcesFetcher: () => (): AvailableSourceGroup[] => [
    {
      code: 'system',
      label: 'System',
      children: [
        {
          code: 'category',
          label: 'Categories',
          type: 'property',
        },
        {
          code: 'enabled',
          label: 'Activé',
          type: 'property',
        },
      ],
    },
    {
      code: 'marketing',
      label: 'Marketing',
      children: [
        {
          code: 'name',
          label: 'Nom',
          type: 'attribute',
        },
        {
          code: 'description',
          label: 'Description',
          type: 'attribute',
        },
      ],
    },
  ],
}));

test('it add attribute source', async () => {
  const handleSourceSelected = jest.fn();

  renderWithProviders(<AddSourceDropdown onSourceSelected={handleSourceSelected} />);
  await act(async () => {
    userEvent.click(screen.getByText('akeneo.tailored_export.column_details.sources.add'));
  });

  expect(screen.getByText('System')).toBeInTheDocument();
  expect(screen.getByText('Categories')).toBeInTheDocument();
  expect(screen.getByText('Activé')).toBeInTheDocument();
  expect(screen.getByText('Marketing')).toBeInTheDocument();
  expect(screen.getByText('Nom')).toBeInTheDocument();
  expect(screen.getByText('Description')).toBeInTheDocument();

  userEvent.click(screen.getByText('Nom'));
  expect(handleSourceSelected).lastCalledWith('name', 'attribute');
});

test('it add property source', async () => {
  const handleSourceSelected = jest.fn();

  renderWithProviders(<AddSourceDropdown onSourceSelected={handleSourceSelected} />);
  await act(async () => {
    userEvent.click(screen.getByText('akeneo.tailored_export.column_details.sources.add'));
  });

  userEvent.click(screen.getByText('Categories'));
  expect(handleSourceSelected).lastCalledWith('category', 'property');
});
