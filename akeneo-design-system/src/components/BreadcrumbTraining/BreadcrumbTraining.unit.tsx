import React from 'react';
import {BreadcrumbTraining} from './BreadcrumbTraining';
import {render, screen} from '../../storybook/test-util';

test('it renders its children properly', () => {
  render(<BreadcrumbTraining>BreadcrumbTraining content</BreadcrumbTraining>);

  expect(screen.getByText('BreadcrumbTraining content')).toBeInTheDocument();
});

// Those tests should pass directly if you follow the contributing guide.
// If you add required props to your Component, these tests will fail
// and you will need to add these required props here as well
test('BreadcrumbTraining supports forwardRef', () => {
  const ref = {current: null};

  render(<BreadcrumbTraining ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('BreadcrumbTraining supports ...rest props', () => {
  render(<BreadcrumbTraining data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
