import React from 'react';
import {render, screen} from '../../storybook/test-util';
import {Avatar} from './Avatar';
import {Avatars} from './Avatars';

test('renders multiple avatars', () => {
  render(
    <Avatars max={2}>
      <Avatar username="john" firstName="John" lastName="Doe" />
      <Avatar username="leonard" firstName="Leonard" lastName="Doe" />
    </Avatars>
  );

  expect(screen.getByTitle('John Doe')).toBeInTheDocument();
  expect(screen.getByTitle('Leonard Doe')).toBeInTheDocument();
});

test('renders a maximum number of avatars', () => {
  render(
    <Avatars max={1}>
      <Avatar username="john" firstName="John" lastName="Doe" />
      <Avatar username="leonard" firstName="Leonard" lastName="Doe" />
    </Avatars>
  );

  expect(screen.getByTitle('John Doe')).toBeInTheDocument();
  expect(screen.queryByTitle('Leonard Doe')).not.toBeInTheDocument();
  expect(screen.getByText('+1')).toBeInTheDocument();
});

test('renders no avatar', () => {
  render(<Avatars max={1}></Avatars>);

  expect(screen.queryByTitle('+1')).not.toBeInTheDocument();
});

test('supports ...rest props', () => {
  render(<Avatars max={1} data-testid="my_value" />);

  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
