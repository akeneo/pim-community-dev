import React from 'react';
import {SquadDemo} from './SquadDemo';
import {render} from '../../storybook/test-util';
import '@testing-library/jest-dom/extend-expect';

test('it renders its children properly', () => {
  const {getByText} = render(<SquadDemo>SquadDemo content</SquadDemo>);

  expect(getByText('SquadDemo content')).toBeInTheDocument();
});
