import React from 'react';
import {Table} from '../Table';
import {render, screen} from '../../../storybook/test-util';

test('it renders its children properly', () => {
  render(
    <Table>
      <Table.Header>
        <Table.HeaderCell>An value</Table.HeaderCell>
      </Table.Header>
    </Table>
  );

  expect(screen.getByText('An value')).toBeInTheDocument();
});

// Those tests should pass directly if you follow the contributing guide.
// If you add required props to your Component, these tests will fail
// and you will need to add these required props here as well
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
