import React from 'react';
import {SwitcherButton} from './SwitcherButton';
import {render, screen} from '../../storybook/test-util';

test('it renders its children properly', () => {
  render(<SwitcherButton>SwitcherButton content</SwitcherButton>);

  expect(screen.getByText('SwitcherButton content')).toBeInTheDocument();
});

// Those tests should pass directly if you follow the contributing guide.
// If you add required props to your Component, these tests will fail
// and you will need to add these required props here as well
test('SwitcherButton supports forwardRef', () => {
  const ref = {current: null};

  render(<SwitcherButton ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('SwitcherButton supports ...rest props', () => {
  render(<SwitcherButton data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
