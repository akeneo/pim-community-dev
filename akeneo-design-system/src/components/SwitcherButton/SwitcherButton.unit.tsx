import React from 'react';
import {SwitcherButton} from './SwitcherButton';
import {fireEvent, render, screen} from '../../storybook/test-util';

test('it renders its children properly', () => {
  render(<SwitcherButton label={'A label'}>A value</SwitcherButton>);

  expect(screen.getByText('A label:')).toBeInTheDocument();
  expect(screen.getByText('A value')).toBeInTheDocument();
});

test('SwitcherButton supports forwardRef', () => {
  const ref = {current: null};

  render(<SwitcherButton label={'A label'} ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('SwitcherButton supports ...rest props', () => {
  render(<SwitcherButton label={'A label'} data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});

test('It calls deletion', () => {
  const onDelete = jest.fn();

  render(<SwitcherButton label={'A label'} inline={false} deletable={true} onDelete={onDelete} />);

  fireEvent.click(screen.getAllByRole('button')[1]);
  expect(onDelete).toBeCalledTimes(1);
});

test('It calls click', () => {
  const onClick = jest.fn();

  render(<SwitcherButton label={'A label'} onClick={onClick} />);

  fireEvent.click(screen.getAllByRole('button')[0]);
  expect(onClick).toBeCalledTimes(1);
});
