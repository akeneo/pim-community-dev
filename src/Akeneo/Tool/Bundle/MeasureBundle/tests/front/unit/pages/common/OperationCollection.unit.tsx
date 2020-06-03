'use strict';

import React from 'react';
import * as ReactDOM from 'react-dom';
import '@testing-library/jest-dom/extend-expect';
import {act, fireEvent, getAllByRole, getByText, getAllByTitle} from '@testing-library/react';
import {AkeneoThemeProvider} from '@akeneo-pim-community/shared';
import {OperationCollection} from 'akeneomeasure/pages/common/OperationCollection';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';

let container: HTMLElement;

beforeEach(() => {
  container = document.createElement('div');
  document.body.appendChild(container);
});

afterEach(() => {
  document.body.removeChild(container);
  global.fetch && global.fetch.mockClear();
  delete global.fetch;
});

test('It renders without errors', async () => {
  await act(async () => {
    ReactDOM.render(
      <DependenciesProvider>
        <AkeneoThemeProvider>
          <OperationCollection operations={[]} onOperationsChange={() => {}} />
        </AkeneoThemeProvider>
      </DependenciesProvider>,
      container
    );
  });
});

test('It renders the given operations', async () => {
  const operations = [
    {
      value: '12',
      operator: 'div',
    },
    {
      value: '25',
      operator: 'mul',
    },
    {
      value: '54',
      operator: 'add',
    },
  ];

  await act(async () => {
    ReactDOM.render(
      <DependenciesProvider>
        <AkeneoThemeProvider>
          <OperationCollection operations={operations} onOperationsChange={() => {}} />
        </AkeneoThemeProvider>
      </DependenciesProvider>,
      container
    );
  });

  const valueInputs = getAllByRole(container, 'operation-value-input') as HTMLInputElement[];

  expect(getByText(container, 'measurements.unit.operator.mul')).toBeInTheDocument();
  expect(getByText(container, 'measurements.unit.operator.div')).toBeInTheDocument();
  expect(getByText(container, 'measurements.unit.operator.add')).toBeInTheDocument();
  expect(valueInputs[0].value).toEqual('12');
  expect(valueInputs[1].value).toEqual('25');
  expect(valueInputs[2].value).toEqual('54');
});

test('I can add an operation', async () => {
  const operations = [
    {
      value: '12',
      operator: 'div',
    },
    {
      value: '25',
      operator: 'add',
    },
  ];
  let newOperations = [];

  await act(async () => {
    ReactDOM.render(
      <DependenciesProvider>
        <AkeneoThemeProvider>
          <OperationCollection
            operations={operations}
            onOperationsChange={updatedOperations => {
              newOperations = updatedOperations;
            }}
          />
        </AkeneoThemeProvider>
      </DependenciesProvider>,
      container
    );
  });

  expect(newOperations).toEqual([]);

  await act(async () => {
    const addButton = getByText(container, 'measurements.unit.operation.add');
    fireEvent.click(addButton);
  });

  expect(newOperations).toEqual([
    {
      value: '12',
      operator: 'div',
    },
    {
      value: '25',
      operator: 'add',
    },
    {value: '', operator: 'mul'},
  ]);
});

test('I can remove an operation', async () => {
  const operations = [
    {
      value: '12',
      operator: 'div',
    },
    {
      value: '25',
      operator: 'add',
    },
  ];
  let newOperations = [];

  await act(async () => {
    ReactDOM.render(
      <DependenciesProvider>
        <AkeneoThemeProvider>
          <OperationCollection
            operations={operations}
            onOperationsChange={updatedOperations => {
              newOperations = updatedOperations;
            }}
          />
        </AkeneoThemeProvider>
      </DependenciesProvider>,
      container
    );
  });

  expect(newOperations).toEqual([]);

  await act(async () => {
    const removeButton = getAllByTitle(container, 'pim_common.remove')[0];
    fireEvent.click(removeButton);
  });

  expect(newOperations).toEqual([
    {
      value: '25',
      operator: 'add',
    },
  ]);
});

test('I can edit the value of an operation', async () => {
  const operations = [
    {
      value: '12',
      operator: 'div',
    },
    {
      value: '25',
      operator: 'add',
    },
  ];
  let newOperations = [];

  await act(async () => {
    ReactDOM.render(
      <DependenciesProvider>
        <AkeneoThemeProvider>
          <OperationCollection
            operations={operations}
            onOperationsChange={updatedOperations => {
              newOperations = updatedOperations;
            }}
          />
        </AkeneoThemeProvider>
      </DependenciesProvider>,
      container
    );
  });

  expect(newOperations).toEqual([]);

  await act(async () => {
    const valueInput = getAllByRole(container, 'operation-value-input')[0];
    fireEvent.change(valueInput, {target: {value: '23'}});
  });

  expect(newOperations).toEqual([
    {
      value: '23',
      operator: 'div',
    },
    {
      value: '25',
      operator: 'add',
    },
  ]);
});

test('I can edit the operator of an operation', async () => {
  const operations = [
    {
      value: '12',
      operator: 'div',
    },
    {
      value: '25',
      operator: 'add',
    },
  ];
  let newOperations = [];

  await act(async () => {
    ReactDOM.render(
      <DependenciesProvider>
        <AkeneoThemeProvider>
          <OperationCollection
            operations={operations}
            onOperationsChange={updatedOperations => {
              newOperations = updatedOperations;
            }}
          />
        </AkeneoThemeProvider>
      </DependenciesProvider>,
      container
    );
  });

  expect(newOperations).toEqual([]);

  await act(async () => {
    const divButton = getByText(container, 'measurements.unit.operator.div');
    fireEvent.click(divButton);
  });

  await act(async () => {
    const subButton = getByText(container, 'measurements.unit.operator.sub');
    fireEvent.click(subButton);
  });

  expect(newOperations).toEqual([
    {
      value: '12',
      operator: 'sub',
    },
    {
      value: '25',
      operator: 'add',
    },
  ]);
});

test('It renders the given operations errors', async () => {
  const operations = [
    {
      value: '12',
      operator: 'div',
    },
    {
      value: '25',
      operator: 'mul',
    },
    {
      value: '54',
      operator: 'add',
    },
  ];

  await act(async () => {
    ReactDOM.render(
      <DependenciesProvider>
        <AkeneoThemeProvider>
          <OperationCollection
            operations={operations}
            onOperationsChange={() => {}}
            errors={[{propertyPath: '', message: 'message', messageTemplate: 'message', parameters: {}}]}
          />
        </AkeneoThemeProvider>
      </DependenciesProvider>,
      container
    );
  });

  expect(getByText(container, 'message')).toBeInTheDocument();
});
