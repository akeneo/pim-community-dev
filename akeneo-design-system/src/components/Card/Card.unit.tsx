import React from 'react';
import {fireEvent, render} from 'storybook/test-util';
import {Card} from './Card';
import {Badge} from '../../components';

test('it renders its children properly', () => {
  const {getByText} = render(
    <Card src="some.jpg">
      <Badge>100%</Badge>Card text
    </Card>
  );

  expect(getByText('Card text')).toBeInTheDocument();
  expect(getByText('100%')).toBeInTheDocument();
});

test('it calls onChange handler when clicked on', () => {
  const onChange = jest.fn();
  const {getByText} = render(
    <Card src="some.jpg" isSelected={false} onSelectCard={onChange}>
      Card text
    </Card>
  );

  fireEvent.click(getByText('Card text'));

  expect(onChange).toBeCalledWith(true);
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
