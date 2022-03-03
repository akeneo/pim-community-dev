import React from 'react';
import {renderWithProviders} from 'feature/tests';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {ErrorAction} from './models';
import {GlobalSettingsTab} from './GlobalSettingsTab';

jest.mock('./components/GlobalSettings/ErrorActionInput', () => ({
  ErrorActionInput: ({onChange}: {onChange: (errorAction: ErrorAction) => void}) => (
    <>
      <button onClick={() => onChange('skip_product')}>Change action on error</button>
    </>
  ),
}));

test('it can update the action on error', () => {
  const handleGlobalSettingsChange = jest.fn();
  renderWithProviders(
    <GlobalSettingsTab
      globalSettings={{
        error_action: 'skip_value',
      }}
      validationErrors={[]}
      onGlobalSettingsChange={handleGlobalSettingsChange}
    />
  );

  userEvent.click(screen.getByText('Change action on error'));

  expect(handleGlobalSettingsChange).toHaveBeenCalledWith({
    error_action: 'skip_product',
  });
});
