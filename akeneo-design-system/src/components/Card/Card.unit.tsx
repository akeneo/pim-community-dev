import React from 'react';
import {fireEvent, render} from 'storybook/test-util';
import {Card, CardGrid} from './Card';
import {Badge} from '../../components';

test('it renders its children properly', () => {
  const {getByText} = render(
    <CardGrid>
      <Card src="some.jpg">
        <Badge>100%</Badge>Card text
      </Card>
    </CardGrid>
  );

  expect(getByText('Card text')).toBeInTheDocument();
  expect(getByText('100%')).toBeInTheDocument();
});

test('it calls onSelectCard handler when clicked on', () => {
  const onSelectCard = jest.fn();
  const {getByText} = render(
    <Card src="some.jpg" isSelected={false} onSelectCard={onSelectCard}>
      Card text
    </Card>
  );

  fireEvent.click(getByText('Card text'));

  expect(onSelectCard).toBeCalledWith(true);
  expect(onSelectCard).toBeCalledTimes(1);
});

test('it calls onSelectCard handler only once when clicking on the Checkbox', () => {
  const onSelectCard = jest.fn();
  const {getByRole} = render(
    <Card src="some.jpg" isSelected={false} onSelectCard={onSelectCard}>
      Card text
    </Card>
  );

  fireEvent.click(getByRole('checkbox'));

  expect(onSelectCard).toBeCalledWith(true);
  expect(onSelectCard).toBeCalledTimes(1);
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
    <Card src="some.jpg" onSelectCard={jest.fn()}>
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
