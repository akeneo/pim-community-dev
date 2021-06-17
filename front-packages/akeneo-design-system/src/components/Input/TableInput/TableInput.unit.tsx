import React from 'react';
import {TableInput} from './TableInput';
import {render, screen} from '../../../storybook/test-util';

test('it renders its children properly', () => {
  render(<TableInput>Table content</TableInput>);

  expect(screen.getByText('Table content')).toBeInTheDocument();
});

// Those tests should pass directly if you follow the contributing guide.
// If you add required props to your Component, these tests will fail
// and you will need to add these required props here as well
test('TableInput supports forwardRef', () => {
  const ref = {current: null};

  render(<TableInput ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('TableInput supports ...rest props', () => {
  render(<TableInput data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
