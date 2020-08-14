import React from 'react';
import {render} from '@testing-library/react';
import {Dummy} from './Dummy';
// TODO check this import '@testing-library/jest-dom/extend-expect';

it('has a href attribute when rendering with linkWrapper', () => {
  const {container, getByText} = render(<Dummy>Nice</Dummy>);

  expect(container.firstChild).not.toBeNull();
  expect(getByText('Nice')).not.toBeNull();
});
