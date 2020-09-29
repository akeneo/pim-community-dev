import React from 'react';
import {Badge} from './Badge';
import {render} from '../../storybook/test-util';

test('it renders its children properly', () => {
  const {getByText} = render(<Badge>Badge content</Badge>);

  expect(getByText('Badge content')).toBeInTheDocument();
});
