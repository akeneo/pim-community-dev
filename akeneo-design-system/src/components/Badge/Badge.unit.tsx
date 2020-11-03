import React from 'react';
import {Badge} from './Badge';
import {render} from '../../storybook/test-util';

test('it renders its children properly', () => {
  const {getByText} = render(<Badge>Badge content</Badge>);

  expect(getByText('Badge content')).toBeInTheDocument();
});

describe('Badge supports forwardRef', () => {
  const ref = {current: null};

  render(<Badge ref={ref} />);
  expect(ref.current).not.toBe(null);
});

describe('Badge supports ...rest props', () => {
    const {container} = render(<Badge data-my-attribute="my_value" />);
    expect(container.querySelector('[data-my-attribute="my_value"]')).toBeInTheDocument();
});
