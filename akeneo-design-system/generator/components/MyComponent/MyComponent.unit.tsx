import React from 'react';
import {MyComponent} from './MyComponent';
import {render} from '../../storybook/test-util';
import '@testing-library/jest-dom/extend-expect';

test('it renders its children properly', () => {
  const {getByText} = render(<MyComponent>MyComponent content</MyComponent>);

  expect(getByText('MyComponent content')).toBeInTheDocument();
});

// Those tests should pass directly if you follow the contributing guide.
// If they fail, maybe you need to customize your implementation forward ref and ...rest props
describe('MyComponent supports forwardRef', () => {
  const ref = {current: null};

  render(<MyComponent ref={ref} />);
  expect(ref.current).not.toBe(null);
});

describe('MyComponent supports ...rest props', () => {
    const {container} = render(<MyComponent data-my-attribute="my_value" />);
    expect(container.querySelector('[data-my-attribute="my_value"]')).toBeInTheDocument();
});
