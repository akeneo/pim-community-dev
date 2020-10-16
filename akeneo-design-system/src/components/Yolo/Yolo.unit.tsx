import React from 'react';
import {Yolo} from './Yolo';
import {render} from '../../storybook/test-util';
import '@testing-library/jest-dom/extend-expect';

test('it renders its children properly', () => {
  const {getByText} = render(<Yolo>Yolo content</Yolo>);

  expect(getByText('Yolo content')).toBeInTheDocument();
});
