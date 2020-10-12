import React from 'react';
import {Coucou} from './Coucou';
import {render} from '../../storybook/test-util';
import '@testing-library/jest-dom/extend-expect';

test('it renders its children properly', () => {
  const {getByText} = render(<Coucou>Coucou content</Coucou>);

  expect(getByText('Coucou content')).toBeInTheDocument();
});
