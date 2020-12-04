import React from 'react';
import {MietteDePain} from './MietteDePain';
import {render, screen} from '../../storybook/test-util';

test('it renders its children properly', () => {
  render(<MietteDePain>MietteDePain content</MietteDePain>);

  expect(screen.getByText('MietteDePain content')).toBeInTheDocument();
});

// Those tests should pass directly if you follow the contributing guide.
// If you add required props to your Component, these tests will fail
// and you will need to add these required props here as well
test('MietteDePain supports forwardRef', () => {
  const ref = {current: null};

  render(<MietteDePain ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('MietteDePain supports ...rest props', () => {
  render(<MietteDePain data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
