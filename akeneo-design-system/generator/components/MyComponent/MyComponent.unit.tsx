import React from 'react';
import {MyComponent} from './MyComponent';
import {render, screen} from '../../storybook/test-util';

test('it renders its children properly', () => {
  render(<MyComponent>MyComponent content</MyComponent>);

  expect(screen.getByText('MyComponent content')).toBeInTheDocument();
});

// Those tests should pass directly if you follow the contributing guide.
// If you add required props to your Component, these tests will fail
// and you will need to add these required props here as well
describe('MyComponent supports forwardRef', () => {
  const ref = {current: null};

  render(<MyComponent ref={ref} />);
  expect(ref.current).not.toBe(null);
});

describe('MyComponent supports ...rest props', () => {
  const {container} = render(<MyComponent data-my-attribute="my_value" />);
  expect(container.querySelector('[data-my-attribute="my_value"]')).toBeInTheDocument();
});
