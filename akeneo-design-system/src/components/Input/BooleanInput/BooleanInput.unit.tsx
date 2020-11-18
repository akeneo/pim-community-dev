import React from 'react';
import {BooleanInput} from './BooleanInput';
import {render, screen} from '../../storybook/test-util';

test('it renders its children properly', () => {
  render(<BooleanInput>BooleanInput content</BooleanInput>);

  expect(screen.getByText('BooleanInput content')).toBeInTheDocument();
});

// Those tests should pass directly if you follow the contributing guide.
// If you add required props to your Component, these tests will fail
// and you will need to add these required props here as well
describe('BooleanInput supports forwardRef', () => {
  const ref = {current: null};

  render(<BooleanInput ref={ref} />);
  expect(ref.current).not.toBe(null);
});

describe('BooleanInput supports ...rest props', () => {
  const {container} = render(<BooleanInput data-my-attribute="my_value" />);
  expect(container.querySelector('[data-my-attribute="my_value"]')).toBeInTheDocument();
});
