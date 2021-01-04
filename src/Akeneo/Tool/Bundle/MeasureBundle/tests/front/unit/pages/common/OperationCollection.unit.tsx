import React from 'react';
import {fireEvent, screen} from '@testing-library/react';
import {OperationCollection} from 'akeneomeasure/pages/common/OperationCollection';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';

test('It renders the given operations', () => {
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

  renderWithProviders(<OperationCollection operations={operations} onOperationsChange={() => {}} />);

  const valueInputs = screen.getAllByPlaceholderText('measurements.unit.operation.placeholder') as HTMLInputElement[];

  expect(screen.getByText('measurements.unit.operator.mul')).toBeInTheDocument();
  expect(screen.getByText('measurements.unit.operator.div')).toBeInTheDocument();
  expect(screen.getByText('measurements.unit.operator.add')).toBeInTheDocument();
  expect(valueInputs[0].value).toEqual('12');
  expect(valueInputs[1].value).toEqual('25');
  expect(valueInputs[2].value).toEqual('54');
});

test('I can add an operation', () => {
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

  renderWithProviders(
    <OperationCollection
      operations={operations}
      onOperationsChange={updatedOperations => {
        newOperations = updatedOperations;
      }}
    />
  );

  expect(newOperations).toEqual([]);

  fireEvent.click(screen.getByText('measurements.unit.operation.add'));

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

test('I can remove an operation', () => {
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

  const {getAllByTitle} = renderWithProviders(
    <OperationCollection
      operations={operations}
      onOperationsChange={updatedOperations => {
        newOperations = updatedOperations;
      }}
    />
  );

  expect(newOperations).toEqual([]);

  fireEvent.click(getAllByTitle('pim_common.remove')[0]);

  expect(newOperations).toEqual([
    {
      value: '25',
      operator: 'add',
    },
  ]);
});

test('I can edit the value of an operation', () => {
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

  renderWithProviders(
    <OperationCollection
      operations={operations}
      onOperationsChange={updatedOperations => {
        newOperations = updatedOperations;
      }}
    />
  );

  expect(newOperations).toEqual([]);

  fireEvent.change(screen.getAllByPlaceholderText('measurements.unit.operation.placeholder')[0], {
    target: {value: '23'},
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

test('I can edit the operator of an operation', () => {
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

  renderWithProviders(
    <OperationCollection
      operations={operations}
      onOperationsChange={updatedOperations => {
        newOperations = updatedOperations;
      }}
    />
  );

  expect(newOperations).toEqual([]);

  fireEvent.click(screen.getByText('measurements.unit.operator.div'));
  fireEvent.click(screen.getByText('measurements.unit.operator.sub'));

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

test('It renders the given operations errors', () => {
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

  renderWithProviders(
    <OperationCollection
      operations={operations}
      onOperationsChange={() => {}}
      errors={[{propertyPath: '', message: 'message', messageTemplate: 'message', parameters: {}}]}
    />
  );

  expect(screen.getByText('message')).toBeInTheDocument();
});
