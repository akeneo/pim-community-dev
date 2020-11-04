import React from 'react';
import 'jest-fetch-mock';
import {renderWithProviders, screen} from '../../../../../../../test-utils';
import {clearAttributeRepositoryCache} from '../../../../../../../src/repositories/AttributeRepository';
import {createAttribute} from '../../../../../factories';
import {AttributeType} from '../../../../../../../src/models';
import {ConcatenatePreview} from '../../../../../../../src/pages/EditRules/components/actions/concatenate/ConcatenatePreview';

jest.mock('../../../../../../../src/fetch/categoryTree.fetcher.ts');

const toRegister = [{name: '`content.actions[0].from`', type: 'custom'}];

describe('ConcatenatePreview', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
    clearAttributeRepositoryCache();
  });

  it('should display the preview of a concatenate action', async () => {
    const defaultValues = {
      content: {
        actions: [
          {
            type: 'concatenate',
            to: {
              field: 'description',
              locale: 'en_US',
              scope: 'mobile',
            },
            from: [
              {
                field: 'name',
              },
              {
                new_line: null,
              },
              {
                text: ' this is a text',
              },
            ],
          },
        ],
      },
    };
    const nameAttribute = createAttribute({
      code: 'name',
      type: AttributeType.TEXT,
      labels: {
        en_US: 'Name',
        fr_FR: 'Nom',
      },
      localizable: false,
      scopable: false,
    });
    fetchMock.mockResponse((request: Request) => {
      if (
        request.url.includes('pim_enrich_attribute_rest_get') &&
        request.url.includes('name')
      ) {
        return Promise.resolve(JSON.stringify(nameAttribute));
      }
      throw new Error(`The "${request.url}" url is not mocked.`);
    });

    renderWithProviders(
      <ConcatenatePreview lineNumber={0} />,
      {all: true},
      {defaultValues, toRegister}
    );

    expect(
      await screen.findByText('pimee_catalog_rule.form.edit.preview')
    ).toBeInTheDocument();
    const calculatePreview = await screen.findByTestId('concatenate-preview');
    expect(calculatePreview.innerHTML).toBe(
      '<span><span class="AknRule-attribute">Name</span></span><span><br></span><span> this is a text</span>'
    );
  });
});
