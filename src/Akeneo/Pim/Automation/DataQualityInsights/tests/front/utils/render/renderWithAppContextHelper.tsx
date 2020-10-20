import React, {FC, ReactElement} from 'react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {Provider} from 'react-redux';

import {render} from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';


import {createStoreWithInitialState} from '@akeneo-pim-community/data-quality-insights/src/infrastructure/store/productEditFormStore';

const renderWithAppContextHelper = (ui: ReactElement, appState = {}) => {
  const store = createStoreWithInitialState(appState);

  const Wrapper: FC = ({children}) => (
    <ThemeProvider theme={pimTheme}>
      <Provider store={store}>{children}</Provider>
    </ThemeProvider>
  );

  return render(ui, {
    wrapper: Wrapper,
  });
};

export {renderWithAppContextHelper};
