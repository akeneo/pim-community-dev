import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import CompletenessBadge from 'akeneoassetmanager/application/component/asset/list/mosaic/completeness-badge';

test('It displays a completeness badge with a calculated ratio', () => {
  const completeness = {complete: 2, required: 4}; // Ratio: 50%

  renderWithProviders(<CompletenessBadge completeness={completeness} />);

  expect(screen.getByText('50%')).toBeInTheDocument();
});
