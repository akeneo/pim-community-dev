import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {PropertyOperations} from './PropertyOperations';
import {Source} from '../../../models';

jest.mock('./PropertySelector', () => ({
  PropertySelector: ({onSelectionChange}: {onSelectionChange: () => void}) => (
    <button onClick={onSelectionChange}>This is a selector</button>
  ),
}));

test.each(['categories'])('it renders a selector for "%s" property', propertyName => {
  const onSourceChange = jest.fn();

  const source: Source = {
    uuid: '22',
    code: propertyName,
    channel: null,
    locale: null,
    operations: [],
    selection: {type: 'code'},
    type: 'property',
  };

  renderWithProviders(<PropertyOperations source={source} validationErrors={[]} onSourceChange={onSourceChange} />);

  userEvent.click(screen.getByText('This is a selector'));

  expect(onSourceChange).toHaveBeenCalled();
});
