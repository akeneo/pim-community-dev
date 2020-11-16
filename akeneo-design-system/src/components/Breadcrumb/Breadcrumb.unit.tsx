import React from 'react';
import {Breadcrumb} from './Breadcrumb';
import {render, screen} from '../../storybook/test-util';

test('it renders its children properly', () => {
  render(<Breadcrumb>Breadcrumb content</Breadcrumb>);

  expect(screen.getByText('Breadcrumb content')).toBeInTheDocument();
});

// Those tests should pass directly if you follow the contributing guide.
// If you add required props to your Component, these tests will fail
// and you will need to add these required props here as well
describe('Breadcrumb supports forwardRef', () => {
  const ref = {current: null};

  render(<Breadcrumb ref={ref} />);
  expect(ref.current).not.toBe(null);
});

describe('Breadcrumb supports ...rest props', () => {
  const {container} = render(<Breadcrumb data-my-attribute="my_value" />);
  expect(container.querySelector('[data-my-attribute="my_value"]')).toBeInTheDocument();
});
