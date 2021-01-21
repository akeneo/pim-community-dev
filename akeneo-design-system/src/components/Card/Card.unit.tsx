import React from 'react';
import {fireEvent, render, screen} from 'storybook/test-util';
import {Card, CardGrid} from './Card';
import {Badge} from '../../components';

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

test('it calls onSelect handler only once when clicking on the Checkbox', () => {
  const onSelect = jest.fn();
  render(
    <Card src="some.jpg" isSelected={false} onSelect={onSelect}>
      Card text
    </Card>
  );

  fireEvent.click(screen.getByRole('checkbox'));

  expect(onSelect).toBeCalledWith(true);
  expect(onSelect).toBeCalledTimes(1);
});

test('it does not call onSelect handler only if onClick is defined', () => {
  const onSelect = jest.fn();
  const onClick = jest.fn();
  render(
    <Card src="some.jpg" isSelected={false} onSelect={onSelect} onClick={onClick}>
      Card text
    </Card>
  );

  fireEvent.click(screen.getByText('Card text'));

  expect(onSelect).not.toBeCalled();
  expect(onClick).toBeCalled();
  expect(onClick).toBeCalledTimes(1);
});

test('it calls onSelect handler if onClick is defined but checkbox is clicked', () => {
  const onSelect = jest.fn();
  const onClick = jest.fn();
  render(
    <Card src="some.jpg" isSelected={false} onSelect={onSelect} onClick={onClick}>
      Card text
    </Card>
  );

  fireEvent.click(screen.getByRole('checkbox'));

  expect(onClick).not.toBeCalled();
  expect(onSelect).toBeCalled();
  expect(onSelect).toBeCalledTimes(1);
});

test('it does not display a Checkbox if no handler is provided', () => {
  const {queryByRole} = render(
    <Card src="some.jpg">
      <Badge>100%</Badge>Card text
    </Card>
  );

  expect(queryByRole('checkbox')).not.toBeInTheDocument();
});

test('it displays a Checkbox if a handler is provided', () => {
  const {queryByRole} = render(
    <Card src="some.jpg" onSelect={jest.fn()}>
      <Badge>100%</Badge>Card text
    </Card>
  );

  expect(queryByRole('checkbox')).toBeInTheDocument();
});

test('it throws when trying to pass unsupported elements as children', () => {
  const mockConsole = jest.spyOn(console, 'error').mockImplementation();

  const cardRender = () =>
    render(
      <Card src="some.jpg">
        <div>Bad div</div>
      </Card>
    );

  expect(cardRender).toThrowError();

  mockConsole.mockRestore();
});

describe('Card supports ...rest props', () => {
  const {container} = render(
    <Card src="some.jpg" data-my-attribute="my_value">
      My card
    </Card>
  );
  expect(container.querySelector('[data-my-attribute="my_value"]')).toBeInTheDocument();
});
