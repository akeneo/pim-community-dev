import React from 'react';
import {ThemeProvider} from "styled-components";
import {onboarderTheme} from 'akeneo-design-system';
import {Test} from "./Test";

function App() {
  return (
      <ThemeProvider theme={onboarderTheme}>
        <Test/>
      </ThemeProvider>
  );
}

export default App;
