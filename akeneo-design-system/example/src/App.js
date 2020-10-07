import React from 'react';
import {Badge, CheckIcon} from 'akeneo-design-system'
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';

function App() {
  return (
    <div>
      <ThemeProvider theme={pimTheme}>
        <Badge>Success</Badge>
        <hr/>
        <CheckIcon width={24} height={24}/>
      </ThemeProvider>
    </div>
  );
}

export default App;
