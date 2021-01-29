/* import React from 'react';
import {BreadcrumbBh} from './BreadcrumbBh';
import {render, screen} from '../../storybook/test-util';

test('it renders its children properly', () => {
  render(
    <BreadcrumbBh>
      {' '}
      <BreadcrumbBh.Item href="http://www.akeneo.com"></BreadcrumbBh.Item>
    </BreadcrumbBh>
  );

  expect(screen.getByText('BreadcrumbBh content')).toBeInTheDocument();
});

// Those tests should pass directly if you follow the contributing guide.
// If you add required props to your Component, these tests will fail
// and you will need to add these required props here as well
test('BreadcrumbBh supports forwardRef', () => {
  const ref = {current: null};

  render(<BreadcrumbBh ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('BreadcrumbBh supports ...rest props', () => {
  render(<BreadcrumbBh data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
 */