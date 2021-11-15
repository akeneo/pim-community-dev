import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {screen} from '@testing-library/react';
import {CatalogVolumeMonitoringApp} from './CatalogVolumeMonitoringApp';

test('it renders column details', async () => {
  renderWithProviders(<CatalogVolumeMonitoringApp />);

  expect(screen.getByText('Work in progess...')).toBeInTheDocument();
});
