import React from 'react';
import {TextAreaInput} from './TextAreaInput';
import {render, screen} from '../../storybook/test-util';

test('it renders its children properly', () => {
  render(<TextAreaInput>TextAreaInput content</TextAreaInput>);

  expect(screen.getByText('TextAreaInput content')).toBeInTheDocument();
});

// Those tests should pass directly if you follow the contributing guide.
// If you add required props to your Component, these tests will fail
// and you will need to add these required props here as well
test('TextAreaInput supports forwardRef', () => {
  const ref = {current: null};

  render(<TextAreaInput ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('TextAreaInput supports ...rest props', () => {
  render(<TextAreaInput data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
