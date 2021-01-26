import React from 'react';
import {fireEvent, render, screen} from '../../storybook/test-util';
import {Button} from './Button';
import userEvent from '@testing-library/user-event';

test('it calls onClick handler when user clicks on button', () => {
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

test('it calls onClick handler when user hits enter key on button', () => {
  const onClick = jest.fn();
  render(
    <Button ghost={true} onClick={onClick}>
      Hello
    </Button>
  );

  const button = screen.getByText('Hello');
  button.focus();
  userEvent.type(button, '{enter}');

  expect(onClick).toBeCalled();
});

test('it does not call onClick handler when user clicks on a disabled button', () => {
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

test('it does not call onClick handler when user hits enter key on a disabled button', () => {
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

test('it displays an anchor when providing a `href`', () => {
  render(<Button href="https://akeneo.com/">Hello</Button>);

  expect(screen.getByText('Hello').closest('a')).toHaveAttribute('href', 'https://akeneo.com/');
});

test('it throws when trying to pass href and onClick', () => {
  const mockConsole = jest.spyOn(console, 'error').mockImplementation();

  expect(() => {
    render(
      <Button onClick={jest.fn()} href="#nice">
        Hello
      </Button>
    );
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
  const onClick = jest.fn();
  render(<Button onClick={undefined}>Hello</Button>);

  fireEvent.click(screen.getByText('Hello'));

  expect(onClick).not.toBeCalled();
});

test('Button supports forwardRef', () => {
  const ref = {current: null};

  render(
    <Button onClick={jest.fn()} ref={ref}>
      My button
    </Button>
  );
  expect(ref.current).not.toBe(null);
});

test('Button supports ...rest props', () => {
  const {container} = render(
    <Button onClick={jest.fn()} data-my-attribute="my_value">
      My button
    </Button>
  );
  expect(container.querySelector('[data-my-attribute="my_value"]')).toBeInTheDocument();
});
