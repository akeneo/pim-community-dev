import React from 'react';
import {Dummy} from './Dummy';
import {render} from '../../storybook/test-util';
import '@testing-library/jest-dom/extend-expect';

test('it renders its children properly', () => {
  const {getByText} = render(<Dummy>Dummy content</Dummy>);

  expect(getByText('Dummy content')).toBeInTheDocument();
});
