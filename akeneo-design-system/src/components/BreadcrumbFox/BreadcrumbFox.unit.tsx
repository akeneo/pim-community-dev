import React from 'react';
import {BreadcrumbFox} from './BreadcrumbFox';
import {render, screen} from '../../storybook/test-util';

test('it renders its children properly', () => {
  render(<BreadcrumbFox>BreadcrumbFox content</BreadcrumbFox>);

  expect(screen.getByText('BreadcrumbFox content')).toBeInTheDocument();
});

// Those tests should pass directly if you follow the contributing guide.
// If you add required props to your Component, these tests will fail
// and you will need to add these required props here as well
test('BreadcrumbFox supports forwardRef', () => {
  const ref = {current: null};

  render(<BreadcrumbFox ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('BreadcrumbFox supports ...rest props', () => {
  render(<BreadcrumbFox data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
