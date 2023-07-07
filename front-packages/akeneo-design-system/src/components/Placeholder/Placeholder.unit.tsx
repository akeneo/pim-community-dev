import React from 'react';
import {render, screen} from 'storybook/test-util';
import {Placeholder} from './Placeholder';
import {UsersIllustration} from '../../illustrations/UsersIllustration';

test('it renders its children properly', () => {
  render(
    <Placeholder illustration={<UsersIllustration />} title="Placeholder title">
      Placeholder text
    </Placeholder>
  );

  expect(screen.getByText('Placeholder title')).toBeInTheDocument();
  expect(screen.getByText('Placeholder text')).toBeInTheDocument();
});

test('Placeholder supports ...rest props', () => {
  render(
    <Placeholder illustration={<UsersIllustration />} title="Placeholder title" data-testid="my_value">
      My Placeholder
    </Placeholder>
  );

  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
