import React from 'react';
import {render} from '../../storybook/test-util';
import {Link} from './Link';

describe('A link', () => {
  it('it redirect user when user clicks on link', () => {
    const {getByText} = render(<Link href="https://akeneo.com">Hello</Link>);

    const link = getByText('Hello');
    expect((link as HTMLAnchorElement).href).toBe('https://akeneo.com/');
  });

  it('it does not redirect user when user clicks on disabled link', () => {
    const {getByText} = render(
      <Link href="https://akeneo.com" disabled={true}>
        Hello
      </Link>
    );

    const link = getByText('Hello');
    expect((link as HTMLAnchorElement).href).not.toBe('https://akeneo.com/');
  });
});
