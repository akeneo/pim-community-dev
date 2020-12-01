import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import EmptyResult from 'akeneoassetmanager/application/component/asset/list/mosaic/empty-result';

test('It displays a notification', () => {
  renderWithProviders(<EmptyResult maxSelectionCount={2000} />);

  expect(screen.getByText('pim_asset_manager.asset_picker.no_result.title')).toBeInTheDocument();
  expect(screen.getByText('pim_asset_manager.asset_picker.no_result.sub_title')).toBeInTheDocument();
});
