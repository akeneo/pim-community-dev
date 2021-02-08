import React from 'react';
import {SectionTitle} from './SectionTitle';
import {render, screen} from '../../storybook/test-util';
import {Button, IconButton, MoreIcon} from '../../';

test('it renders its children properly', () => {
  render(
    <SectionTitle>
      <SectionTitle.Title>General parameters</SectionTitle.Title>
      <SectionTitle.Spacer />
      <SectionTitle.Information>10 results</SectionTitle.Information>
      <SectionTitle.Separator />
      <Button>Action</Button>
      <IconButton icon={<MoreIcon />} title='More'/>
    </SectionTitle>
  );

  expect(screen.getByText('General parameters')).toBeInTheDocument();
  expect(screen.getByText('10 results')).toBeInTheDocument();
  expect(screen.getByText('Action')).toBeInTheDocument();
  expect(screen.getByTitle('More')).toBeInTheDocument();
});

test('SectionTitle supports ...rest props', () => {
  render(<SectionTitle data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
