import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {SourceFooter} from './SourceFooter';
import userEvent from '@testing-library/user-event';
import {Source} from '../../models';

test('It handles source removal', () => {
  const handleRemove = jest.fn();
  const source: Source = {
    channel: null,
    code: 'category',
    locale: 'en_US',
    operations: [],
    selection: {
      type: 'code',
    },
    type: 'property',
    uuid: '266b6361-badb-48a1-98ef-d75baa235148',
  };

  renderWithProviders(<SourceFooter source={source} onSourceRemove={handleRemove} />);

  const removeButton = screen.getByText('akeneo.tailored_export.column_details.sources.remove.button');
  userEvent.click(removeButton);

  const confirmButton = screen.getByText('pim_common.delete');
  userEvent.click(confirmButton);

  expect(handleRemove).toHaveBeenCalledTimes(1);
  expect(handleRemove).toHaveBeenCalledWith(source);
});
