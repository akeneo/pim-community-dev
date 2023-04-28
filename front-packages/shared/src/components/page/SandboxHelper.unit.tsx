import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '../../tests/utils';
import {SandboxHelper} from './SandboxHelper';

let mockedFeatureFlags: string[] = ['sandbox_banner'];

jest.mock('@akeneo-pim-community/shared/src/hooks/useFeatureFlags', () => ({
  useFeatureFlags: () => ({
    isEnabled: (featureFlag: string) => mockedFeatureFlags.includes(featureFlag),
  }),
}));

test('it renders its content', () => {
  renderWithProviders(<SandboxHelper />);

  expect(screen.getByText('pim_system.sandbox.helper.text')).toBeInTheDocument();
});

test('it renders nothing if the feature flag is not enabled', () => {
  mockedFeatureFlags = [];

  renderWithProviders(<SandboxHelper />);

  expect(screen.queryByText('pim_system.sandbox.helper.text')).not.toBeInTheDocument();
});
