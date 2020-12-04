import React from 'react';
import {MietteDePain} from './MietteDePain';
import {render, screen} from '../../storybook/test-util';

test('it renders its children properly', () => {
  render(
    <MietteDePain>
      <MietteDePain.Miette>Ma miette 1</MietteDePain.Miette>
      <MietteDePain.Miette>Ma miette 2</MietteDePain.Miette>
    </MietteDePain>
  );

  expect(screen.getByText('Ma miette 1')).toBeInTheDocument();
  expect(screen.getByText('Ma miette 2')).toBeInTheDocument();
});

test('it renders only Miettes', () => {
  render(
    <MietteDePain>
      <MietteDePain.Miette>Ma miette</MietteDePain.Miette>
      <MietteDePain.Separator>Separateur</MietteDePain.Separator>
    </MietteDePain>
  );

  expect(screen.queryByText('Separateur')).not.toBeInTheDocument();
});

test('MietteDePain supports ...rest props', () => {
  render(<MietteDePain data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
