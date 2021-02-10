import React from 'react';
import {BreadcrumbOctopus} from './BreadcrumbOctopus';
import {render, screen} from '../../storybook/test-util';

test('it renders its children properly', () => {
  render(<BreadcrumbOctopus>BreadcrumbOctopus content</BreadcrumbOctopus>);

  expect(screen.getByText('BreadcrumbOctopus content')).toBeInTheDocument();
});

// Those tests should pass directly if you follow the contributing guide.
// If you add required props to your Component, these tests will fail
// and you will need to add these required props here as well
test('BreadcrumbOctopus supports forwardRef', () => {
  const ref = {current: null};

  render(<BreadcrumbOctopus ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('BreadcrumbOctopus supports ...rest props', () => {
  render(<BreadcrumbOctopus data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
