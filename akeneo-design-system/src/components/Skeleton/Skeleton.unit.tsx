import React from 'react';
import {Skeleton} from './Skeleton';
import {render, screen} from '../../storybook/test-util';

test('it renders its children properly', () => {
  render(<Skeleton skeleton={true}>Skeleton content</Skeleton>);

  expect(screen.getByText('Skeleton content')).toBeInTheDocument();
});
