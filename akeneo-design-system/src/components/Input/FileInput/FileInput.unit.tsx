import React from 'react';
import {FileInput} from './FileInput';
import {render, screen} from '../../storybook/test-util';

test('it renders its children properly', () => {
  render(<FileInput>FileInput content</FileInput>);

  expect(screen.getByText('FileInput content')).toBeInTheDocument();
});

// Those tests should pass directly if you follow the contributing guide.
// If you add required props to your Component, these tests will fail
// and you will need to add these required props here as well
test('FileInput supports forwardRef', () => {
  const ref = {current: null};

  render(<FileInput ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('FileInput supports ...rest props', () => {
  render(<FileInput data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
