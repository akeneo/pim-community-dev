'use strict';

import React from 'react';
import * as ReactDOM from 'react-dom';
import '@testing-library/jest-dom/extend-expect';
import {act, fireEvent, getByLabelText, getByText, getByTitle} from '@testing-library/react';
import {AkeneoThemeProvider} from 'akeneomeasure/AkeneoThemeProvider';
import {OperationCollection} from 'akeneomeasure/pages/common/OperationCollection';

// const changeTextInputValue = (container: HTMLElement, label: string, value: string) => {
//   const input = getByLabelText(container, label, {exact: false, trim: true});
//   fireEvent.change(input, {target: {value: value}});
// };

// const getFormSectionByTitle = (container: HTMLElement, title: string): HTMLElement => {
//   const header = getByText(container, title);
//   return header.parentElement as HTMLElement;
// };

const getByInputValue = (container: HTMLElement, value: string) => {
  return container.querySelector(`input[value="${value}"]`);
};

const getFirstByTitle = (container: HTMLElement, value: string) => {
  return container.querySelector(`[title="${value}"]`);
};

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
      <AkeneoThemeProvider>
        <OperationCollection operations={[]} onOperationsChange={() => {}} />
      </AkeneoThemeProvider>,
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
      operator: 'mul',
    },
  ];
  await act(async () => {
    ReactDOM.render(
      <AkeneoThemeProvider>
        <OperationCollection operations={operations} onOperationsChange={() => {}} />
      </AkeneoThemeProvider>,
      container
    );
  });

  expect(getByText(container, 'measurements.unit.operator.mul')).toBeInTheDocument();
  expect(getByInputValue(container, '12')).toBeInTheDocument();
  expect(getByInputValue(container, '25')).toBeInTheDocument();
  expect(getByText(container, 'measurements.unit.operator.div')).toBeInTheDocument();
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
      <AkeneoThemeProvider>
        <OperationCollection
          operations={operations}
          onOperationsChange={updatedOperations => {
            newOperations = updatedOperations;
          }}
        />
      </AkeneoThemeProvider>,
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
    {value: '1', operator: 'mul'},
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
      <AkeneoThemeProvider>
        <OperationCollection
          operations={operations}
          onOperationsChange={updatedOperations => {
            newOperations = updatedOperations;
          }}
        />
      </AkeneoThemeProvider>,
      container
    );
  });

  expect(newOperations).toEqual([]);

  await act(async () => {
    const removeButton = getFirstByTitle(container, 'pim_common.remove');
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
      <AkeneoThemeProvider>
        <OperationCollection
          operations={operations}
          onOperationsChange={updatedOperations => {
            newOperations = updatedOperations;
          }}
        />
      </AkeneoThemeProvider>,
      container
    );
  });

  expect(newOperations).toEqual([]);

  await act(async () => {
    const valueInput = getByInputValue(container, '12');
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
      <AkeneoThemeProvider>
        <OperationCollection
          operations={operations}
          onOperationsChange={updatedOperations => {
            newOperations = updatedOperations;
          }}
        />
      </AkeneoThemeProvider>,
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
