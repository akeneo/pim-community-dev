import React from 'react';
import {SelectInput} from './SelectInput';
import {render, screen} from '../../storybook/test-util';

test('it renders its children properly', () => {
  render(<SelectInput>SelectInput content</SelectInput>);

  expect(screen.getByText('SelectInput content')).toBeInTheDocument();
});

// Those tests should pass directly if you follow the contributing guide.
// If you add required props to your Component, these tests will fail
// and you will need to add these required props here as well
test('SelectInput supports forwardRef', () => {
  const ref = {current: null};

  render(<SelectInput ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('SelectInput supports ...rest props', () => {
  render(<SelectInput data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
