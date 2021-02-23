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
  expect(onSelect).toBeCalledTimes(1);
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

test('it displays a stack style when the card is marked as stacked', () => {
  render(
    <Card src="some.jpg" stacked>
      <Card.BadgeContainer>
        <Badge>100%</Badge>
      </Card.BadgeContainer>
      Card text
    </Card>
  );

  expect(screen.getByTestId('stack')).toBeInTheDocument();
});

describe('Card supports ...rest props', () => {
  render(
    <Card src="some.jpg" data-testid="my_value">
      My card
    </Card>
  );

  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
