import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {SourceFooter} from './SourceFooter';
import {Source} from '../../models';

test('It handles source removal', () => {
  const handleRemove = jest.fn();
  const source: Source = {
    code: 'parent',
    channel: null,
    locale: null,
    operations: [],
    selection: {
      type: 'code',
    },
    type: 'property',
    uuid: '266b6361-badb-48a1-98ef-d75baa235148',
  };

  renderWithProviders(<SourceFooter source={source} onSourceRemove={handleRemove} />);

  userEvent.click(screen.getByText('akeneo.tailored_export.column_details.sources.remove.button'));
  userEvent.click(screen.getByText('pim_common.confirm'));

  expect(handleRemove).toHaveBeenCalledTimes(1);
  expect(handleRemove).toHaveBeenCalledWith(source);
});
