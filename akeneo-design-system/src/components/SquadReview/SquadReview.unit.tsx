import React from 'react';
import {SquadReview} from './SquadReview';
import {render} from '../../storybook/test-util';
import '@testing-library/jest-dom/extend-expect';

test('it renders its children properly', () => {
  const {getByText} = render(<SquadReview>SquadReview content</SquadReview>);

  expect(getByText('SquadReview content')).toBeInTheDocument();
});
