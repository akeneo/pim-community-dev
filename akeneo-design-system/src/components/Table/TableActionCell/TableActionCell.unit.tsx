import React from 'react';
import {Table} from '../Table';
import {fireEvent, render, screen} from '../../../storybook/test-util';
import {Button, IconButton} from '../..';
import {AkeneoIcon} from '../../../icons';

test('it renders its children properly', () => {
  render(
    <Table>
      <Table.Body>
        <Table.Row>
          <Table.ActionCell>A value</Table.ActionCell>
        </Table.Row>
      </Table.Body>
    </Table>
  );

  expect(screen.getByText('A value')).toBeInTheDocument();
});

test('it stops event propagation when the user clicks on a Button', () => {
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

test('it stops event propagation when the user clicks on an IconButton', () => {
  const onRowClick = jest.fn();
  const onIconButtonClick = jest.fn();

  render(
    <Table>
      <Table.Body>
        <Table.Row onClick={onRowClick}>
          <Table.ActionCell>
            <IconButton icon={<AkeneoIcon />} title="an icon button" onClick={onIconButtonClick} />
          </Table.ActionCell>
        </Table.Row>
      </Table.Body>
    </Table>
  );

  const iconButton = screen.getByTitle('an icon button');
  fireEvent.click(iconButton);

  expect(onIconButtonClick).toBeCalled();
  expect(onRowClick).not.toBeCalled();
});

test('Table.ActionCell supports forwardRef', () => {
  const ref = {current: null};
  render(
    <Table>
      <Table.Body>
        <Table.Row>
          <Table.ActionCell ref={ref}>A value</Table.ActionCell>
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
