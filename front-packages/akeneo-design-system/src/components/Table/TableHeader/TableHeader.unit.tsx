import React from 'react';
import {Table} from '../Table';
import {render, screen} from '../../../storybook/test-util';

test('it renders its children properly', () => {
  render(
    <Table>
      <Table.Header>
        <Table.HeaderCell>A value</Table.HeaderCell>
      </Table.Header>
    </Table>
  );

  expect(screen.getByText('A value')).toBeInTheDocument();
});

test('Table.Header supports forwardRef', () => {
  const ref = {current: null};
  render(
    <Table>
      <Table.Header ref={ref} />
    </Table>
  );

  expect(ref.current).not.toBe(null);
});

test('Table.Header supports ...rest props', () => {
  render(
    <Table>
      <Table.Header data-testid="my_value" />
    </Table>
  );

  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
