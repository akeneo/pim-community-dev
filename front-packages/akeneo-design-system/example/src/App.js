import React from 'react';
import {ThemeProvider} from 'styled-components';
import {Badge, CheckIcon, pimTheme} from 'akeneo-design-system';

function App() {
  return (
    <div>
      <ThemeProvider theme={pimTheme}>
        <Badge>Success</Badge>
        <hr />
        <CheckIcon size={24} />
      </ThemeProvider>
    </div>
  );
}

export default App;
