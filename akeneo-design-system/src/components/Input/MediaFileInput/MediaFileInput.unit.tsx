import React from 'react';
import {MediaFileInput} from './MediaFileInput';
import {render, screen} from '../../../storybook/test-util';

test('it renders its children properly', () => {
  render(<MediaFileInput>MediaFileInput content</MediaFileInput>);

  expect(screen.getByText('MediaFileInput content')).toBeInTheDocument();
});

// Those tests should pass directly if you follow the contributing guide.
// If you add required props to your Component, these tests will fail
// and you will need to add these required props here as well
test('MediaFileInput supports forwardRef', () => {
  const ref = {current: null};

  render(<MediaFileInput ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('MediaFileInput supports ...rest props', () => {
  render(<MediaFileInput data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
