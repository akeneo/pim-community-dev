import React from 'react';
import {Table} from '../Table';
import {render, screen} from '../../../storybook/test-util';

test('it renders its children properly', () => {
  render(
    <Table>
      <Table.Body>
        <Table.Row>
          <Table.Cell isHighlighted>A value</Table.Cell>
          <Table.Cell>Another value</Table.Cell>
        </Table.Row>
      </Table.Body>
    </Table>
  );

  expect(screen.getByText('A value')).toBeInTheDocument();
  expect(screen.getByText('Another value')).toBeInTheDocument();
});

test('Table.Cell supports forwardRef', () => {
  const ref = {current: null};
  render(
    <Table>
      <Table.Body>
        <Table.Row>
          <Table.Cell ref={ref}>A value</Table.Cell>
        </Table.Row>
      </Table.Body>
    </Table>
  );

  expect(ref.current).not.toBe(null);
});

test('Table.Cell supports ...rest props', () => {
  render(
    <Table>
      <Table.Body>
        <Table.Row>
          <Table.Cell data-testid="my_value" />
        </Table.Row>
      </Table.Body>
    </Table>
  );

  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
