import React from 'react';
import styled from 'styled-components';
import {Badge} from './Badge';
import {render, screen} from '../../storybook/test-util';

test('it renders its children properly', () => {
  render(<Badge>Badge content</Badge>);

  expect(screen.getByText('Badge content')).toBeInTheDocument();
});

test('its style can be overridden', () => {
  const StyledBadge = styled(Badge)`
    width: 200px;
  `;

  render(<StyledBadge>StyledBadge content</StyledBadge>);

  expect(screen.getByText('StyledBadge content')).toBeInTheDocument();
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
