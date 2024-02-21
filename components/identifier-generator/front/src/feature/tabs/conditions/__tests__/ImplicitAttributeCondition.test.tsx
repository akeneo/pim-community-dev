import React from 'react';
import {render} from '../../../tests/test-utils';
import {ImplicitAttributeCondition} from '../ImplicitAttributeCondition';
import {AbbreviationType, PROPERTY_NAMES, SimpleSelectProperty} from '../../../models';
import {waitFor} from '@testing-library/react';
import mockedScopes from '../../../tests/fixtures/scopes';

describe('ImplicitAttributeCondition', () => {
  it('should render implicit attribute localizable and scopable conditions', async () => {
    mockSimpleSelectCalls({localizable: true, scopable: true});

    const property: SimpleSelectProperty = {
      type: PROPERTY_NAMES.SIMPLE_SELECT,
      attributeCode: 'brand',
      process: {type: AbbreviationType.NO},
      scope: 'ecommerce',
      locale: 'en_US',
    };

    const screen = render(
      <table>
        <tbody>
          <ImplicitAttributeCondition
            attributeCode={property.attributeCode}
            scope={property.scope}
            locale={property.locale}
          />
        </tbody>
      </table>
    );

    await waitFor(() => expect(screen.getByText('Simple select')).toBeInTheDocument());
    await waitFor(() => expect(screen.getByPlaceholderText('pim_common.channel')).toBeInTheDocument());
    await waitFor(() => expect(screen.getByText('Ecommerce')).toBeInTheDocument());
    await waitFor(() => expect(screen.getByPlaceholderText('pim_common.locale')).toBeInTheDocument());
    await waitFor(() => expect(screen.getByText('English (United States)')).toBeInTheDocument());
  });

  it('should render implicit simple select non localizable and non scopable', async () => {
    mockSimpleSelectCalls();

    const property: SimpleSelectProperty = {
      type: PROPERTY_NAMES.SIMPLE_SELECT,
      attributeCode: 'brand',
      process: {type: AbbreviationType.NO},
    };

    const screen = render(
      <table>
        <tbody>
          <ImplicitAttributeCondition
            attributeCode={property.attributeCode}
            scope={property.scope}
            locale={property.locale}
          />
        </tbody>
      </table>
    );

    await waitFor(() => expect(screen.getByText('Simple select')).toBeInTheDocument());
    expect(screen.queryByPlaceholderText('pim_common.channel')).not.toBeInTheDocument();
    expect(screen.queryByPlaceholderText('pim_common.locale')).not.toBeInTheDocument();
  });
});

const mockSimpleSelectCalls = ({localizable = false, scopable = false, inError = false, errorStatus = ''} = {}) => {
  const fetchImplementation = jest.fn().mockImplementation((requestUrl: string, args: {method: string}) => {
    if (requestUrl === 'akeneo_identifier_generator_get_attribute_options') {
      return Promise.resolve({
        ok: true,
        json: () =>
          Promise.resolve([
            {code: 'option_a', labels: {en_US: 'OptionA'}},
            {code: 'option_b', labels: {en_US: 'OptionB'}},
            {code: 'option_c', labels: {en_US: 'OptionC'}},
            {code: 'option_d', labels: {en_US: 'OptionD'}},
            {code: 'option_e', labels: {en_US: 'OptionE'}},
          ]),
        statusText: '',
        status: 200,
      } as Response);
    } else if (requestUrl === 'pim_enrich_channel_rest_index') {
      return Promise.resolve({
        ok: true,
        json: () => Promise.resolve(mockedScopes),
        statusText: '',
        status: 200,
      } as Response);
    } else if (requestUrl === 'pim_enrich_attribute_rest_get') {
      if (inError) {
        jest.spyOn(console, 'error');
        // eslint-disable-next-line no-console
        (console.error as jest.Mock).mockImplementation(() => null);
        return Promise.resolve({
          ok: false,
          json: () => Promise.resolve(),
          statusText: errorStatus,
          status: Number.parseFloat(errorStatus),
        } as Response);
      } else {
        return Promise.resolve({
          ok: true,
          json: () =>
            Promise.resolve({
              code: 'simple_select',
              labels: {en_US: 'Simple select', fr_FR: 'Select simple'},
              localizable,
              scopable,
            }),
          statusText: '',
          status: 200,
        } as Response);
      }
    }

    throw new Error(`Unmocked url "${requestUrl}" [${args.method}]`);
  });
  jest.spyOn(global, 'fetch').mockImplementation(fetchImplementation);
};
