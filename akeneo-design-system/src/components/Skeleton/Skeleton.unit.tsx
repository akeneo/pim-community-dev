import React from 'react';
import {Skeleton} from './Skeleton';
import {render, screen} from '../../storybook/test-util';

test('it renders its children properly', () => {
  render(<Skeleton>Skeleton content</Skeleton>);

  expect(screen.getByText('Skeleton content')).toBeInTheDocument();
});
