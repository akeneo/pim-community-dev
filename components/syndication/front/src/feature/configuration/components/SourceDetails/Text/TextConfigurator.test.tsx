import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {TextConfigurator} from './TextConfigurator';
import {getDefaultTextSource} from './model';
import {getDefaultDateSource} from '../Date/model';

const attribute = {
  code: 'text',
  type: 'pim_catalog_text',
  labels: {},
  scopable: false,
  localizable: false,
  is_locale_specific: false,
  available_locales: [],
};

jest.mock('../common/DefaultValue');
jest.mock('./CleanHTMLTags');

test('it can update default value operation', () => {
  const onSourceChange = jest.fn();
  const requirement = {
    code: 'text',
    label: 'Text',
    help: '',
    group: '',
    examples: [],
    type: 'string' as const,
    required: false,
  };

  renderWithProviders(
    <TextConfigurator
      requirement={requirement}
      attribute={attribute}
      source={{
        ...getDefaultTextSource(attribute, null, null),
        uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
      }}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  userEvent.click(screen.getByText('Default value'));

  expect(onSourceChange).toHaveBeenCalledWith({
    ...getDefaultTextSource(attribute, null, null),
    operations: {
      default_value: {
        type: 'default_value',
        value: 'foo',
      },
    },
    selection: {
      type: 'code',
    },
    uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
  });
});

test('it can update clean html tags operation', () => {
  const onSourceChange = jest.fn();
  const requirement = {
    code: 'text',
    label: 'Text',
    help: '',
    group: '',
    examples: [],
    type: 'string' as const,
    required: false,
  };

  renderWithProviders(
    <TextConfigurator
      requirement={requirement}
      attribute={attribute}
      source={{
        ...getDefaultTextSource(attribute, null, null),
        uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
      }}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  userEvent.click(screen.getByText('Clean HTML tags'));

  expect(onSourceChange).toHaveBeenCalledWith({
    ...getDefaultTextSource(attribute, null, null),
    operations: {
      clean_html_tags: {
        type: 'clean_html_tags',
        value: true,
      },
    },
    selection: {
      type: 'code',
    },
    uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
  });
});

test('it tells when the source data is invalid', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const dateAttribute = {...attribute, type: 'pim_catalog_date', code: 'date_attribute'};
  const requirement = {
    code: 'text',
    label: 'Text',
    help: '',
    group: '',
    examples: [],
    type: 'string' as const,
    required: false,
  };

  expect(() => {
    renderWithProviders(
      <TextConfigurator
        requirement={requirement}
        source={getDefaultDateSource(dateAttribute, null, null)}
        attribute={dateAttribute}
        validationErrors={[]}
        onSourceChange={jest.fn()}
      />
    );
  }).toThrow('Invalid source data "date_attribute" for text configurator');

  mockedConsole.mockRestore();
});
