import React from 'react';
import {screen, fireEvent} from '@testing-library/react';
import {SourceTabBar} from './SourceTabBar';
import {Source} from '../../models';
import {renderWithProviders} from 'feature/tests';

test('it renders the source tab bar', async () => {
  const handleTabChange = jest.fn();
  const sources: Source[] = [
    {
      uuid: 'cffd560e-1e40-4c55-a415-89c7958b270d',
      code: 'description',
      type: 'attribute',
      locale: null,
      channel: null,
      operations: [],
      selection: {
        type: 'code',
      },
    },
    {
      uuid: 'cffd540e-1e40-4c55-a415-89c7958b270d',
      code: 'name',
      type: 'attribute',
      locale: null,
      channel: null,
      operations: [],
      selection: {
        type: 'code',
      },
    },
    {
      uuid: 'cffd540e-1e40-4c55-a415-89c7958b280d',
      code: 'categories',
      type: 'property',
      locale: null,
      channel: null,
      operations: [],
      selection: {
        type: 'code',
        separator: ',',
      },
    },
    {
      uuid: '4a871382-9ccb-49e4-958e-0e59c9fdd672',
      code: 'XSELL',
      type: 'association_type',
      locale: null,
      channel: null,
      operations: [],
      selection: {
        type: 'code',
        separator: ',',
        entity_type: 'products',
      },
    },
    {
      uuid: 'a00fcf91-bdb8-48e2-84c3-39f4f66ecb5d',
      code: 'UPSELL',
      type: 'association_type',
      locale: null,
      channel: null,
      operations: [],
      selection: {
        type: 'code',
        separator: ',',
        entity_type: 'products',
      },
    },
  ];

  await renderWithProviders(
    <SourceTabBar
      validationErrors={[]}
      sources={sources}
      currentTab="cffd560e-1e40-4c55-a415-89c7958b270d"
      onTabChange={handleTabChange}
    />
  );

  expect(screen.getByText(/English description/i)).toBeInTheDocument();
  expect(screen.getByText(/English name/i)).toBeInTheDocument();
  expect(screen.getByText(/pim_common.categories/i)).toBeInTheDocument();
  expect(screen.getByText(/Cross sell/i)).toBeInTheDocument();
  expect(screen.getByText('[UPSELL]')).toBeInTheDocument();

  fireEvent.click(screen.getByText(/Name/i));
  expect(handleTabChange).toHaveBeenCalledWith('cffd540e-1e40-4c55-a415-89c7958b270d');
});
