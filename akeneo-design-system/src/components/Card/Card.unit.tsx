import React from 'react';
import {fireEvent, render, screen} from 'storybook/test-util';
import {Card, CardGrid} from './Card';
import {Badge, Link} from '../../components';

test('it renders its children properly', () => {
  render(
    <CardGrid>
      <Card src="some.jpg">
        <Badge>100%</Badge>Card text
      </Card>
    </CardGrid>
  );

  expect(screen.getByText('Card text')).toBeInTheDocument();
  expect(screen.getByText('100%')).toBeInTheDocument();
});

test('it calls onSelect handler when clicked on', () => {
  const onSelect = jest.fn();
  render(
    <Card src="some.jpg" isSelected={false} onSelect={onSelect}>
      Card text
    </Card>
  );

  fireEvent.click(screen.getByText('Card text'));

  expect(onSelect).toBeCalledWith(true);
  expect(onSelect).toBeCalledTimes(1);
});

test('it does not call onSelect or onClick handlers when disabled', () => {
  const onSelect = jest.fn();
  const onClick = jest.fn();

  render(
    <Card src="some.jpg" disabled={true} onClick={onClick} onSelect={onSelect}>
      Card text
    </Card>
  );

  fireEvent.click(screen.getByText('Card text'));

  expect(onSelect).not.toBeCalled();
  expect(onClick).not.toBeCalled();
});

test('it calls onSelect handler only once when clicking on the Checkbox', () => {
  const onSelect = jest.fn();
  render(
    <Card src="some.jpg" isSelected={false} onSelect={onSelect}>
      Card text
    </Card>
  );

  fireEvent.click(screen.getByRole('checkbox'));

  expect(onSelect).toBeCalledWith(true, expect.anything());
  expect(onSelect).toBeCalledTimes(1);
});

test('it does not call onSelect handler if onClick is defined when clicking on the label or the image', () => {
  const onSelect = jest.fn();
  const onClick = jest.fn();
  render(
    <Card src="some.jpg" isSelected={false} onSelect={onSelect} onClick={onClick}>
      Card text
    </Card>
  );

  fireEvent.click(screen.getByText('Card text'));
  fireEvent.click(screen.getByRole('img'));

  expect(onSelect).not.toBeCalled();
  expect(onClick).toBeCalledTimes(2);
});

test('it calls its child Link handler when clicking on the image', () => {
  const onClick = jest.fn();

  render(
    <Card src="some.jpg">
      <Link onClick={onClick}>Card link</Link>
    </Card>
  );

  fireEvent.click(screen.getByRole('img'));

  expect(onClick).toBeCalledTimes(1);
});

test('it does not display a Checkbox if no handler is provided', () => {
  render(
    <Card src="some.jpg">
      <Badge>100%</Badge>Card text
    </Card>
  );

  expect(screen.queryByRole('checkbox')).not.toBeInTheDocument();
});

test('it displays a Checkbox if a handler is provided', () => {
  render(
    <Card src="some.jpg" onSelect={jest.fn()}>
      <Badge>100%</Badge>Card text
    </Card>
  );

  expect(screen.queryByRole('checkbox')).toBeInTheDocument();
});

test('Card supports ...rest props', () => {
  render(
    <Card src="some.jpg" data-testid="my_value">
      My card
    </Card>
  );

  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
