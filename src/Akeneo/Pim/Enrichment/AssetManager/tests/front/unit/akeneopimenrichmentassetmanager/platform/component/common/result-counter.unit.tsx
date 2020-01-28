import * as React from 'react';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import {ThemeProvider} from 'styled-components';
import {render} from '@testing-library/react';
import {ResultCounter} from 'akeneoassetmanager/application/component/app/result-counter';
import '@testing-library/jest-dom/extend-expect';

test('It displays no count', () => {
  const {container} = render(
    <ThemeProvider theme={akeneoTheme}>
      <ResultCounter />
    </ThemeProvider>
  );

  expect(container).toBeEmpty();
});

// The translator is not fully working, hence it displays the translation key
// when it does not a translation for it.
test('It displays a count', () => {
  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <ResultCounter count={10} />
    </ThemeProvider>
  );

  expect(getByText('pim_asset_manager.result_counter')).toBeInTheDocument();
});
