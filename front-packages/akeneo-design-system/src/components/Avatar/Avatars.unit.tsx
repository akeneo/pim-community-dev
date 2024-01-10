import React from 'react';
import {fireEvent, render, screen} from '../../storybook/test-util';
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
  expect(screen.queryByText('LD')).not.toBeInTheDocument();
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

test('displays remaining users names on plus hover', () => {
  const invalidChild = 'I should not be in the title';
  render(
    <Avatars max={1} maxTitle={1}>
      <Avatar username="dSchrute" firstName="Dwight" lastName="Schrute" />
      <Avatar username="mscott" firstName=" " lastName="  " />
      <Avatar username="kMalone" firstName="Kevin" lastName="Malone" />
      {invalidChild}
    </Avatars>
  );

  expect(screen.getByText('DS')).toBeInTheDocument();
  expect(screen.getByText('+3')).toBeInTheDocument();
  // Kevin Malone should not be visible as it should be part of the +1
  expect(screen.queryByText('mscott')).not.toBeInTheDocument();

  fireEvent.mouseOver(screen.getByText('+3'));
  expect(screen.getByTitle('mscott ...')).toBeInTheDocument();
});
