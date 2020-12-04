import React from 'react';
import {Table} from '../Table';
import {render, screen} from '../../../storybook/test-util';

test('it renders its children properly', () => {
  render(
    <Table>
      <Table.Body>
        <Table.Row>
          <Table.Cell>A value</Table.Cell>
        </Table.Row>
      </Table.Body>
    </Table>
  );

  expect(screen.getByText('A value')).toBeInTheDocument();
});

test('Table.Body supports forwardRef', () => {
  const ref = {current: null};
  render(
    <Table>
      <Table.Body ref={ref} />
    </Table>
  );

  expect(ref.current).not.toBe(null);
});

test('Table.Body supports ...rest props', () => {
  render(
    <Table>
      <Table.Body data-testid="my_value" />
    </Table>
  );

  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
