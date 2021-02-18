import React from 'react';
import {render, screen} from '../../storybook/test-util';
import {Link} from './Link';

describe('A link', () => {
  it('it redirect user when user clicks on link', () => {
    render(<Link href="https://akeneo.com">Hello</Link>);

    expect(screen.getByText('Hello')).toHaveAttribute('href', 'https://akeneo.com');
  });

  it('it does not redirect user when user clicks on a disabled link', () => {
    render(
      <Link href="https://akeneo.com" disabled={true}>
        Hello
      </Link>
    );

    expect(screen.getByText('Hello')).not.toHaveAttribute('href', 'https://akeneo.com');
  });

  it('it does not call onClick handler on a disabled link', () => {
    const onClick = jest.fn();

    render(
      <Link onClick={onClick} href="https://akeneo.com" disabled={true}>
        Hello
      </Link>
    );

    expect(onClick).not.toHaveBeenCalled();
  });

  it('it automatically add noopener and noreferrer for security reason when link is open into another tab', () => {
    render(
      <Link href="https://akeneo.com" target="_blank">
        Hello
      </Link>
    );

    expect(screen.getByText('Hello')).toHaveAttribute('target', '_blank');
    expect(screen.getByText('Hello')).toHaveAttribute('rel', 'noopener noreferrer');
  });
});

describe('Link supports forwardRef', () => {
  const ref = {current: null};

  render(<Link ref={ref}>My link</Link>);
  expect(ref.current).not.toBe(null);
});

describe('Link supports ...rest props', () => {
  render(<Link data-testid="my_value">My link</Link>);

  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
