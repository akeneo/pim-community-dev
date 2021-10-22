import React from 'react';
import {renderWithProviders} from '../tests/utils';
import {UnsavedChanges} from './UnsavedChanges';

test('it should display an unsaved change warning', () => {
  const {getByText} = renderWithProviders(<UnsavedChanges />);

  expect(getByText('pim_common.entity_updated')).toBeInTheDocument();
});
