import React from 'react';
import {fireEvent, render, screen} from '../../storybook/test-util';
import {BlockButton} from './BlockButton';
import userEvent from '@testing-library/user-event';
import {PlusIcon, ArrowDownIcon} from '../../icons';

test('it calls onClick handler when user clicks on button', () => {
  const onClick = jest.fn();
  render(
    <BlockButton icon={<ArrowDownIcon />} onClick={onClick}>
      My block button
    </BlockButton>
  );

  const button = screen.getByText('My block button');
  fireEvent.click(button);

  expect(onClick).toBeCalled();
});

test('it calls onClick handler when user hits enter key on button', () => {
  const onClick = jest.fn();
  render(
    <BlockButton icon={<ArrowDownIcon />} onClick={onClick}>
      My block button
    </BlockButton>
  );

  const button = screen.getByText('My block button');
  button.focus();
  userEvent.type(button, '{enter}');

  expect(onClick).toBeCalled();
});

test('it does not call onClick handler when user clicks on a disabled button', () => {
  const onClick = jest.fn();
  render(
    <BlockButton disabled={true} icon={<ArrowDownIcon />} onClick={onClick}>
      My block button
    </BlockButton>
  );

  const button = screen.getByText('My block button');
  fireEvent.click(button);

  expect(onClick).not.toBeCalled();
});

test('it does not call onClick handler when user hits enter key on a disabled button', () => {
  const onClick = jest.fn();
  render(
    <BlockButton disabled={true} icon={<ArrowDownIcon />} onClick={onClick}>
      My block button
    </BlockButton>
  );

  const button = screen.getByText('My block button');
  fireEvent.keyDown(button, {key: 'Enter', code: 'Enter'});

  expect(onClick).not.toBeCalled();
});

test('it does not trigger onClick when disabled', () => {
  const onClick = jest.fn();
  render(
    <BlockButton disabled={true} icon={<ArrowDownIcon />} onClick={onClick}>
      My block button
    </BlockButton>
  );

  const button = screen.getByText('My block button');
  fireEvent.click(button);

  expect(onClick).not.toBeCalled();
});

test('it does not trigger onClick when onClick is undefined', () => {
  const onClick = jest.fn();
  render(
    <BlockButton icon={<ArrowDownIcon />} onClick={undefined}>
      My block button
    </BlockButton>
  );

  fireEvent.click(screen.getByText('My block button'));

  expect(onClick).not.toBeCalled();
});

test('BlockButton supports forwardRef', () => {
  const ref = {current: null};

  render(
    <BlockButton icon={<ArrowDownIcon />} onClick={jest.fn()} ref={ref}>
      My block button
    </BlockButton>
  );

  expect(ref.current).not.toBe(null);
});

test('BlockButton supports ...rest props', () => {
  render(
    <BlockButton icon={<ArrowDownIcon />} onClick={jest.fn()} data-testid="my_value">
      My block button
    </BlockButton>
  );

  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});

test('it renders children with icon', () => {
  render(
    <BlockButton icon={<ArrowDownIcon />}>
      <PlusIcon data-testid="children-icon" /> My block button
    </BlockButton>
  );

  expect(screen.getByText('My block button')).toBeInTheDocument();
  expect(screen.getByTestId('children-icon')).toBeInTheDocument();
});
