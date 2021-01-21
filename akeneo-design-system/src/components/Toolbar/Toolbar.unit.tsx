import React from 'react';
import {Toolbar} from './Toolbar';
import {render, screen} from '../../storybook/test-util';
import {Button, Checkbox} from '..';

test('it renders its children properly', () => {
  render(
    <Toolbar>
      <Toolbar.SelectionContainer>
        <Checkbox checked={true} />
      </Toolbar.SelectionContainer>
      <Toolbar.LabelContainer>Toolbar content</Toolbar.LabelContainer>
      <Toolbar.ActionsContainer>
        <Button level="secondary">Button 1</Button>
        <Button level="tertiary">Button 2</Button>
        <Button level="danger">Button 3</Button>
      </Toolbar.ActionsContainer>
    </Toolbar>
  );

  expect(screen.getByRole('checkbox')).toBeInTheDocument();
  expect(screen.getByText('Toolbar content')).toBeInTheDocument();
  expect(screen.getByText('Button 1')).toBeInTheDocument();
  expect(screen.getByText('Button 2')).toBeInTheDocument();
  expect(screen.getByText('Button 3')).toBeInTheDocument();
});

test('Toolbar supports ...rest props', () => {
  render(<Toolbar data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
