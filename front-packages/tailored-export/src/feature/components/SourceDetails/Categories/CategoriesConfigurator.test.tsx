import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {CategoriesConfigurator} from './CategoriesConfigurator';
import {getDefaultCategoriesSource} from './model';
import {getDefaultParentSource} from '../Parent/model';

jest.mock('../common/CodeLabelCollectionSelector');

test('it displays a categories configurator', () => {
  const onSourceChange = jest.fn();

  renderWithProviders(
    <CategoriesConfigurator
      source={{
        ...getDefaultCategoriesSource(),
        uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
      }}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  userEvent.click(screen.getByText('Update selection'));

  expect(onSourceChange).toHaveBeenCalledWith({
    ...getDefaultCategoriesSource(),
    selection: {
      separator: ',',
      locale: 'en_US',
      type: 'label',
    },
    uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
  });
});

test('it tells when the source data is invalid', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  expect(() => {
    renderWithProviders(
      <CategoriesConfigurator source={getDefaultParentSource()} validationErrors={[]} onSourceChange={jest.fn()} />
    );
  }).toThrow('Invalid source data "parent" for categories configurator');

  expect(screen.queryByText('Update selection')).not.toBeInTheDocument();
  mockedConsole.mockRestore();
});
