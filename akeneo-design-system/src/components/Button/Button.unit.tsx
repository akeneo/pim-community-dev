import React from 'react';
import {fireEvent, render, screen} from '../../storybook/test-util';
import {Button} from './Button';
import userEvent from '@testing-library/user-event';

it('it calls onClick handler when user clicks on button', () => {
  const onClick = jest.fn();
  render(
    <Button size="small" onClick={onClick}>
      Hello
    </Button>
  );

  const button = screen.getByText('Hello');
  fireEvent.click(button);

  expect(onClick).toBeCalled();
});

it('it calls onClick handler when user hit enter key on button', async () => {
  const onClick = jest.fn();
  render(
    <Button ghost={true} onClick={onClick}>
      Hello
    </Button>
  );

  const button = screen.getByText('Hello');
  button.focus();
  await userEvent.type(button, '{enter}');

  expect(onClick).toBeCalled();
});

it('it does not call onClick handler when user clicks on a disabled button', () => {
  const onClick = jest.fn();
  render(
    <Button disabled={true} ghost={true} onClick={onClick}>
      Hello
    </Button>
  );

  const button = screen.getByText('Hello');
  fireEvent.click(button);

  expect(onClick).not.toBeCalled();
});

it('it does not call onClick handler when user hit enter key on button', () => {
  const onClick = jest.fn();
  render(
    <Button disabled={true} onClick={onClick}>
      Hello
    </Button>
  );

  const button = screen.getByText('Hello');
  fireEvent.keyDown(button, {key: 'Enter', code: 'Enter'});

  expect(onClick).not.toBeCalled();
});

it('it displays an anchor when providing a `href`', () => {
  render(<Button href="https://akeneo.com/">Hello</Button>);

  expect(screen.getByText('Hello').closest('a')).toHaveAttribute('href', 'https://akeneo.com/');
});

test('it throws when trying to pass href and onClick', () => {
  const mockConsole = jest.spyOn(console, 'error').mockImplementation();

  const buttonRender = () =>
    render(
      <Button href="http://google.com" onClick={() => {}}>
        invalid button
      </Button>
    );

  expect(buttonRender).toThrowError();

  mockConsole.mockRestore();
});

test('it throws when trying to pass href and onClick', () => {
  const mockConsole = jest.spyOn(console, 'error').mockImplementation();

  expect(() => {
    render(<Button onClick={() => {}} href="#nice">Hello</Button>);
  }).toThrowError();

  mockConsole.mockRestore();
});

test('it does not trigger onClick when disabled', () => {
  const onClick = jest.fn();
  render(
    <Button disabled={true} onClick={onClick}>
      Hello
    </Button>
  );

  const button = screen.getByText('Hello');
  fireEvent.click(button);

  expect(onClick).not.toBeCalled();
});
test('it does not trigger onClick when onClick is undefined', () => {
  render(
    <Button>
      Hello
    </Button>
  );

  const button = screen.getByText('Hello');
  fireEvent.click(button);
});
