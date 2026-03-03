import React from 'react';
import {render, screen} from '../../storybook/test-util';
import {IconCard, IconCardGrid} from './IconCard';
import {ComponentIcon} from '../../icons';
import userEvent from '@testing-library/user-event';

test('It renders a IconCard component', () => {
  render(<IconCard icon={<ComponentIcon />} label="My label" content="Content of the card" />);
  expect(screen.getByText('My label')).toBeInTheDocument();
  expect(screen.getByText('Content of the card')).toBeInTheDocument();
});

test('SwitcherButton supports forwardRef', () => {
  const ref = {current: null};
  render(<IconCard icon={<ComponentIcon />} label="My label" content="Content" ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('It calls the onClick handler when the card is clicked', () => {
  const onClick = jest.fn();
  render(<IconCard icon={<ComponentIcon />} label="My label" content="Content of the card" onClick={onClick} />);

  userEvent.click(screen.getByText('My label'));

  expect(onClick).toBeCalledTimes(1);
});

test('Icon cards car be rendered in a grid', () => {
  render(
    <IconCardGrid>
      <IconCard icon={<ComponentIcon />} label="My label" content="Content" />
      <IconCard icon={<ComponentIcon />} label="My label" content="Content" />
      <IconCard icon={<ComponentIcon />} label="My label" content="Content" />
      <IconCard icon={<ComponentIcon />} label="My label" content="Content" />
      <IconCard icon={<ComponentIcon />} label="My label" content="Content" />
    </IconCardGrid>
  );

  expect(screen.getAllByText('My label').length).toBe(5);
  expect(screen.getAllByText('Content').length).toBe(5);
});
