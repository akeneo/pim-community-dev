import React from 'react';
import {PimVersion} from '@akeneo-pim-community/activity/src/components/PimVersion';
import {act, getByText} from '@testing-library/react';
import {renderDOMWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {usePimVersion} from '@akeneo-pim-community/activity/src/hooks/usePimVersion';

jest.mock('@akeneo-pim-community/activity/src/hooks/usePimVersion');

let container: HTMLElement;
beforeEach(() => {
  container = document.createElement('div');
  document.body.appendChild(container);
});
afterEach(() => {
  document.body.removeChild(container);
});

test('it displays the last version if the current version is not up to date', async () => {
  usePimVersion.mockReturnValue({
    version: 'EE 6.0.20 Maple',
    lastPatch: 'v6.0.21',
  });

  await act(async () => renderDOMWithProviders(<PimVersion />, container as HTMLElement));
  expect(getByText(container, /pim_dashboard\.version/)).toBeInTheDocument();
  expect(getByText(container, /EE 6\.0\.20 Maple/)).toBeInTheDocument();
  expect(getByText(container, /pim_analytics\.new_patch_available/)).toBeInTheDocument();
  expect(getByText(container, /v6\.0\.21/)).toBeInTheDocument();
});

test('it does not display the last version if the current version is up to date', async () => {
  usePimVersion.mockReturnValue({
    version: 'EE 6.0.21 Maple',
    lastPatch: '',
  });

  await act(async () => renderDOMWithProviders(<PimVersion />, container as HTMLElement));
  expect(getByText(container, /pim_dashboard\.version/)).toBeInTheDocument();
  expect(getByText(container, /EE 6\.0\.21 Maple/)).toBeInTheDocument();
});
