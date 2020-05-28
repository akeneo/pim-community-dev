import React from 'react';
import ReactDOM from 'react-dom';
import {act, getByText} from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';
import {AkeneoThemeProvider, InputErrors} from '@akeneo-pim-community/shared';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';

let container: HTMLElement;
beforeEach(() => {
  container = document.createElement('div');
  document.body.appendChild(container);
});

afterEach(() => {
  document.body.removeChild(container);
  container = null;
});

test('It displays input errors', async () => {
  await act(async () => {
    ReactDOM.render(
      <DependenciesProvider>
        <AkeneoThemeProvider>
          <InputErrors
            errors={[
              {
                propertyPath: 'quantity',
                messageTemplate: 'an.error',
                parameters: {
                  limit: '255',
                },
                message: 'an error',
                invalidValue: '10000',
              },
            ]}
          />
        </AkeneoThemeProvider>{' '}
      </DependenciesProvider>,
      container
    );
  });

  expect(getByText(container, 'an.error')).toBeInTheDocument();
});
