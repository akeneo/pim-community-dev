import React from 'react';
import {Table} from './Table';
import {render, screen} from '../../storybook/test-util';

test('it renders its children properly', () => {
  render(<Table>Table content</Table>);

  expect(screen.getByText('Table content')).toBeInTheDocument();
});

// Those tests should pass directly if you follow the contributing guide.
// If you add required props to your Component, these tests will fail
// and you will need to add these required props here as well
describe('Table supports forwardRef', () => {
  const ref = {current: null};

  render(<Table ref={ref} />);
  expect(ref.current).not.toBe(null);
});

describe('Table supports ...rest props', () => {
  const {container} = render(<Table data-my-attribute="my_value" />);
  expect(container.querySelector('[data-my-attribute="my_value"]')).toBeInTheDocument();
});
