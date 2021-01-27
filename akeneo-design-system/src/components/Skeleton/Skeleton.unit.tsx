import React from 'react';
import {render, screen} from '../../storybook/test-util';
import {Skeleton} from './Skeleton';
import {Link} from 'components/Link/Link';
import 'jest-styled-components';

test('it renders its children properly', () => {
  render(<Skeleton>Skeleton content</Skeleton>);

  expect(screen.getByText('Skeleton content')).toBeInTheDocument();
});

test('it renders its children as Skeletons when enabled', () => {
  render(
    <Skeleton enabled={true}>
      String content
      <Link>Hello</Link>
      <div>No skeleton here</div>
    </Skeleton>
  );

  const linkComponent = screen.getByText('Hello');
  const simpleDiv = screen.getByText('No skeleton here');

  expect(screen.getByText('String content')).toBeInTheDocument();
  expect(linkComponent).toBeInTheDocument();
  expect(linkComponent).toHaveStyleRule('animation', expect.stringContaining('infinite'));
  expect(simpleDiv).toBeInTheDocument();
  expect(simpleDiv).not.toHaveStyleRule('animation', expect.stringContaining('infinite'));
});
