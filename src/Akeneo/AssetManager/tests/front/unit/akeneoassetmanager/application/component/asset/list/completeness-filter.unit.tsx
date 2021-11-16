import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {
  CompletenessFilter,
  CompletenessValue,
} from 'akeneoassetmanager/application/component/asset/list/completeness-filter';

test('I can change the completeness value', () => {
  const handleChange = jest.fn();

  renderWithProviders(<CompletenessFilter value={CompletenessValue.All} onChange={handleChange} />);

  userEvent.click(screen.getByLabelText('pim_asset_manager.asset.grid.filter.completeness.label:'));
  userEvent.click(screen.getByText('pim_asset_manager.asset.grid.filter.completeness.yes'));

  expect(handleChange).toHaveBeenCalledWith(CompletenessValue.Yes);
});
