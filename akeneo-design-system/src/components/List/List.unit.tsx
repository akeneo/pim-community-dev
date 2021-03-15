import React from 'react';
import {List} from './List';
import {render, screen} from '../../storybook/test-util';
import {Helper} from '../Helper/Helper';
import {IconButton} from '../IconButton/IconButton';
import {Button} from '../Button/Button';
import {CloseIcon} from '../../icons';

test('it renders its children properly', () => {
  render(
    <List>
      <List.Row>
        <List.TitleCell width="auto">
          A text
        </List.TitleCell>
        <List.Cell width={150}>
          An information
        </List.Cell>
        <List.ActionCell>
          <Button>First action</Button>
          <Button>Second action</Button>
          Not a button
        </List.ActionCell>
        <List.RemoveCell>
          <IconButton ghost="borderless" level="tertiary" icon={<CloseIcon />} title="Remove first item" />
        </List.RemoveCell>
      </List.Row>
      <List.Row>
        <List.TitleCell width="auto">
          Another text
        </List.TitleCell>
        <List.Cell width={150}>
          Another information
        </List.Cell>
        <List.ActionCell>
          <Button>Another action</Button>
        </List.ActionCell>
        <List.RemoveCell>
          <IconButton ghost="borderless" level="tertiary" icon={<CloseIcon />} title="Remove second item" />
        </List.RemoveCell>
        <List.RowHelpers>
          <Helper level="info">An helper</Helper>
        </List.RowHelpers>
      </List.Row>
    </List>
  );

  expect(screen.getByText('A text')).toBeInTheDocument();
  expect(screen.getByText('An information')).toBeInTheDocument();
  expect(screen.getByText('First action')).toBeInTheDocument();
  expect(screen.getByText('Second action')).toBeInTheDocument();
  expect(screen.getByText('Not a button')).toBeInTheDocument();
  expect(screen.getByTitle('Remove first item')).toBeInTheDocument();
  expect(screen.getByText('Another text')).toBeInTheDocument();
  expect(screen.getByText('Another information')).toBeInTheDocument();
  expect(screen.getByText('Another action')).toBeInTheDocument();
  expect(screen.getByText('An helper')).toBeInTheDocument();
  expect(screen.getByTitle('Remove first item')).toBeInTheDocument();
});

test('List supports ...rest props', () => {
  render(<List data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
