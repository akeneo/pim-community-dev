'use strict';

import fetcher from 'akeneoassetmanager/infrastructure/fetcher/attribute';
import * as fetch from 'akeneoassetmanager/tools/fetch';

jest.mock('pim/router', () => {});
jest.mock('pim/security-context', () => {}, {virtual: true});
jest.mock('routing');
jest.mock('akeneoassetmanager/application/configuration/attribute');

describe('akeneoassetmanager/infrastructure/fetcher/attribute', () => {
  it('It lists the attributes of an asset family', async () => {
    // @ts-ignore
    fetch.getJSON = jest.fn().mockImplementationOnce(() =>
      Promise.resolve([
        {
          identifier: 'description_1234',
          asset_family_identifier: 'designer',
          code: 'description',
          is_required: true,
          is_read_only: true,
          order: 0,
          value_per_locale: true,
          value_per_channel: false,
          type: 'text',
          labels: {
            en_US: 'Description',
          },
          max_length: 12,
          is_textarea: true,
          is_rich_text_editor: false,
          validation_rule: 'none',
          regular_expression: null,
        },
        {
          identifier: 'side_view_1234',
          asset_family_identifier: 'designer',
          code: 'side_view',
          is_required: false,
          is_read_only: false,
          order: 1,
          value_per_locale: true,
          value_per_channel: false,
          type: 'media_file',
          labels: {
            en_US: 'Side view',
          },
          max_file_size: '123.4',
          allowed_extensions: ['jpg', 'png'],
          media_type: 'image',
        },
      ])
    );

    const response = await fetcher.fetchAll('designer');

    expect(response).toEqual([
      {
        code: 'description',
        assetFamilyIdentifier: 'designer',
        identifier: 'description_1234',
        isRichTextEditor: false,
        isTextarea: true,
        labelCollection: {en_US: 'Description'},
        maxLength: 12,
        regularExpression: null,
        validationRule: 'none',
        order: 0,
        isRequired: true,
        isReadOnly: true,
        type: 'text',
        valuePerChannel: false,
        valuePerLocale: true,
      },
      {
        code: 'side_view',
        assetFamilyIdentifier: 'designer',
        identifier: 'side_view_1234',
        labelCollection: {
          en_US: 'Side view',
        },
        order: 1,
        isRequired: false,
        isReadOnly: false,
        type: 'media_file',
        valuePerChannel: false,
        valuePerLocale: true,
        allowedExtensions: ['jpg', 'png'],
        maxFileSize: '123.4',
        mediaType: 'image',
      },
    ]);
  });
});
