import * as React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import CompletenessBadge from 'akeneoassetmanager/application/component/asset/list/mosaic/completeness-badge';

test('It displays a completeness badge with a calculated ratio', () => {
  const completeness = {complete: 2, required: 4}; // Ratio: 50%

  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <CompletenessBadge completeness={completeness} />
    </ThemeProvider>
  );

  expect(getByText('50%')).toBeInTheDocument();
});
