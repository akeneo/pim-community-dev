import React from 'react';
import {PimVersion} from '@akeneo-pim-community/activity/src/components/PimVersion';
import {act, getByText, queryByText} from '@testing-library/react';
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

test('it displays the version without any new patch message', async () => {
  usePimVersion.mockReturnValue({
    version: 'CE 2026.5 Sahara Hare',
  });

  await act(async () => renderDOMWithProviders(<PimVersion />, container as HTMLElement));
  expect(getByText(container, /pim_dashboard\.version/)).toBeInTheDocument();
  expect(getByText(container, /CE 2026\.5 Sahara Hare/)).toBeInTheDocument();
  expect(queryByText(container, /pim_analytics\.new_patch_available/)).not.toBeInTheDocument();
});
