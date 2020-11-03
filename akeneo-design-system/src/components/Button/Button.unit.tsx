import React from 'react';
import {fireEvent, render} from '../../storybook/test-util';
import {Button} from './Button';

it('it calls onClick handler when user clicks on button', () => {
  const onClick = jest.fn();
  const {getByText} = render(
    <Button size="small" onClick={onClick}>
      Hello
    </Button>
  );

  const button = getByText('Hello');
  fireEvent.click(button);

  expect(onClick).toBeCalled();
});
it('it calls onClick handler when user hit enter key on button', () => {
  const onClick = jest.fn();
  const {getByText} = render(
    <Button ghost={true} onClick={onClick}>
      Hello
    </Button>
  );

  const button = getByText('Hello');
  fireEvent.keyDown(button, {key: 'Enter', code: 'Enter'});

  expect(onClick).toBeCalled();
});

it('it does not call onClick handler when user clicks on a disabled button', () => {
  const onClick = jest.fn();
  const {getByText} = render(
    <Button disabled={true} ghost={true} onClick={onClick}>
      Hello
    </Button>
  );

  const button = getByText('Hello');
  fireEvent.click(button);

  expect(onClick).not.toBeCalled();
});
it('it does not call onClick handler when user hit enter key on button', () => {
  const onClick = jest.fn();
  const {getByText} = render(
    <Button disabled={true} onClick={onClick}>
      Hello
    </Button>
  );

  const button = getByText('Hello');
  fireEvent.keyDown(button, {key: 'Enter', code: 'Enter'});

  expect(onClick).not.toBeCalled();
});

describe('Button supports forwardRef', () => {
  const ref = {current: null};

  render(
    <Button
      onClick={() => {
        // Do nothing
      }}
      ref={ref}
    >
      My button
    </Button>
  );
  expect(ref.current).not.toBe(null);
});

describe('Button supports ...rest props', () => {
  const {container} = render(
    <Button
      onClick={() => {
        // Do nothing
      }}
      data-my-attribute="my_value"
    >
      My button
    </Button>
  );
  expect(container.querySelector('[data-my-attribute="my_value"]')).toBeInTheDocument();
});
