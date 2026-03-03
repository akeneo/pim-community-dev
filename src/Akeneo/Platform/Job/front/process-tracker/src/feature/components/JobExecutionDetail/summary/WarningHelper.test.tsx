import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {WarningHelper} from './WarningHelper';
import {Warning} from '../../../models';

const warning: Warning = {
  reason: 'reason',
  item: {
    code: 'my element',
  },
};

test('it displays a warning', () => {
  renderWithProviders(<WarningHelper warning={warning} />);

  expect(screen.queryByText('my element')).not.toBeInTheDocument();

  userEvent.click(screen.getByText('job_execution.summary.display_item'));

  expect(screen.getByText('my element')).toBeInTheDocument();
});

test('it does not display expand button if item is empty', () => {
  const warningWithEmptyItem: Warning = {
    reason: 'reason',
    item: {},
  };

  renderWithProviders(<WarningHelper warning={warningWithEmptyItem} />);

  expect(screen.queryByText('job_execution.summary.display_item')).not.toBeInTheDocument();
});
