import {TranslateProvider} from '@akeneo-pim-community/legacy';
import React from 'react';
import ReactDOM from 'react-dom';
import App from './App';

ReactDOM.render(
  <React.StrictMode>
    <TranslateProvider value={(id: string) => `translation_from_cra.${id}`}>
      <App />
    </TranslateProvider>
  </React.StrictMode>,
  document.getElementById('root')
);
