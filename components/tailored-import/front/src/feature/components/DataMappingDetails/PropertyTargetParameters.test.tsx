import React from 'react';
import {screen} from '@testing-library/react';
import {PropertyTargetParameters} from './PropertyTargetParameters';
import {PropertyTarget} from '../../models';
import {renderWithProviders} from 'feature/tests';
import userEvent from '@testing-library/user-event';

const propertyTarget: PropertyTarget = {
  code: 'description',
  type: 'property',
  action_if_not_empty: 'set',
  action_if_empty: 'skip',
};

test('it can change the if_empty case when hitting the checkbox', async () => {
  const handleTargetChange = jest.fn();

  await renderWithProviders(<PropertyTargetParameters target={propertyTarget} onTargetChange={handleTargetChange} />);

  userEvent.click(screen.getByLabelText('akeneo.tailored_import.data_mapping.target.clear_if_empty'));

  expect(handleTargetChange).toHaveBeenCalledWith({
    ...propertyTarget,
    action_if_empty: 'clear',
  });
});
