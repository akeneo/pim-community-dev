import React from 'react';
import {Table} from '../Table';
import {fireEvent, render, screen} from '../../../storybook/test-util';
import {Button} from '../..';

test('it renders its children properly', () => {
  render(
    <Table>
      <Table.Body>
        <Table.Row>
          <Table.ActionCell>An value</Table.ActionCell>
        </Table.Row>
      </Table.Body>
    </Table>
  );

  expect(screen.getByText('An value')).toBeInTheDocument();
});

test('it event stop propagation when user click on button', () => {
  const onRowClick = jest.fn();
  const onButtonClick = jest.fn();

  render(
    <Table>
      <Table.Body>
        <Table.Row onClick={onRowClick}>
          <Table.ActionCell>
            <Button onClick={onButtonClick}>My button</Button>
          </Table.ActionCell>
        </Table.Row>
      </Table.Body>
    </Table>
  );

  const button = screen.getByText('My button');
  fireEvent.click(button);

  expect(onButtonClick).toBeCalled();
  expect(onRowClick).not.toBeCalled();
});

// Those tests should pass directly if you follow the contributing guide.
// If you add required props to your Component, these tests will fail
// and you will need to add these required props here as well
test('Table.ActionCell supports forwardRef', () => {
  const ref = {current: null};
  render(
    <Table>
      <Table.Body>
        <Table.Row>
          <Table.ActionCell ref={ref}>An value</Table.ActionCell>
        </Table.Row>
      </Table.Body>
    </Table>
  );

  expect(ref.current).not.toBe(null);
});

test('Table.ActionCell supports ...rest props', () => {
  render(
    <Table>
      <Table.Body>
        <Table.Row>
          <Table.ActionCell data-testid="my_value" />
        </Table.Row>
      </Table.Body>
    </Table>
  );

  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
