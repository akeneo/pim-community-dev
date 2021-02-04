import React from 'react';
import {ProgressIndicator} from './ProgressIndicator';
import {render, screen} from '../../storybook/test-util';

test('it renders its children properly', () => {
  render(<ProgressIndicator>ProgressIndicator content</ProgressIndicator>);

  expect(screen.getByText('ProgressIndicator content')).toBeInTheDocument();
});

// Those tests should pass directly if you follow the contributing guide.
// If you add required props to your Component, these tests will fail
// and you will need to add these required props here as well
test('ProgressIndicator supports forwardRef', () => {
  const ref = {current: null};

  render(<ProgressIndicator ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('ProgressIndicator supports ...rest props', () => {
  render(<ProgressIndicator data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
