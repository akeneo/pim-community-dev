import React from 'react';
import 'jest-fetch-mock';
import {EditRules} from '../../../../src/pages/EditRules/';
import userEvent from '@testing-library/user-event';
import {
  act,
  render,
  fireEvent,
  waitForElementToBeRemoved,
  screen,
} from '../../../../test-utils';
import {Scope} from '../../../../src/models';
import {clearCategoryRepositoryCache} from '../../../../src/repositories/CategoryRepository';
import {clearAttributeRepositoryCache} from '../../../../src/repositories/AttributeRepository';
import {dependencies} from '../../../../src/dependenciesTools/provider/dependencies';
import {locales, uiLocales} from '../../factories';

const ruleDefinitionCode = 'my_code';

const ruleDefinitionPayload = {
  id: 14,
  code: ruleDefinitionCode,
  type: 'product',
  priority: 0,
  enabled: true,
  content: {actions: [], conditions: []},
  labels: {
    en_US: 'My code',
  },
};

const scopesPayload: Scope[] = [
  {
    code: 'ecommerce',
    currencies: ['EUR', 'USD'],
    locales,
    category_tree: 'master',
    conversion_units: [],
    labels: {
      en_US: 'e-commerce',
    },
    meta: {},
  },
];

const addConditionFieldsPayload = [
  {
    id: 'system',
    text: 'System',
    children: [
      {
        id: 'family',
        text: 'Family',
      },
    ],
  },
  {
    id: 'marketing',
    text: 'Marketing',
    children: [
      {
        id: 'name',
        text: 'Name',
      },
    ],
  },
];

const setIsDirty = (_isDirty: boolean) => {};

describe('EditRules', () => {
  afterEach(() => {
    fetchMock.resetMocks();
    clearCategoryRepositoryCache();
    clearAttributeRepositoryCache();
  });

  it('should submit the form with the input data from rule properties', async () => {
    // Given
    fetchMock.mockResponses(
      [JSON.stringify(ruleDefinitionPayload), {status: 200}],
      [JSON.stringify(locales), {status: 200}],
      [JSON.stringify(uiLocales), {status: 200}],
      [JSON.stringify(scopesPayload), {status: 200}]
    );
    fetchMock.mockResponse(() => {
      return new Promise(resolve =>
        setTimeout(() => resolve({body: 'ok'}), 1000)
      );
    });
    // When
    const {getByTestId, findByTestId, findByText, findByLabelText} = render(
      <EditRules
        ruleDefinitionCode={ruleDefinitionCode}
        setIsDirty={setIsDirty}
      />,
      {
        legacy: true,
      }
    );

    const propertiesTab = (await findByText(
      'pim_common.properties'
    )) as HTMLButtonElement;
    userEvent.click(propertiesTab);
    const inputPriority = (await findByLabelText(
      'pimee_catalog_rule.form.edit.priority.label'
    )) as HTMLInputElement;
    const inputLabelUS = (await findByLabelText(
      'English (United States)'
    )) as HTMLInputElement;
    const inputLabelFrench = (await findByLabelText(
      'French (France)'
    )) as HTMLInputElement;
    const inputLabelGerman = (await findByLabelText(
      'German (Germany)'
    )) as HTMLInputElement;
    act(() => {
      userEvent.type(inputPriority, '1');
      userEvent.type(inputLabelUS, 'Hello');
      userEvent.type(inputLabelFrench, 'Salut');
      userEvent.type(inputLabelGerman, 'Hallo');
      fireEvent.submit(getByTestId('edit-rules-form'));
    });
    // Then
    expect(await findByTestId('akeneo-spinner')).toBeInTheDocument();
  });

  it('should render the page with the right title and right labels', async () => {
    // Given
    fetchMock.mockResponse((request: Request) => {
      if (
        request.url.includes(
          'pimee_enrich_rule_definition_get?%7B%22ruleCode%22:%22my_code%22%7D'
        )
      ) {
        return Promise.resolve(JSON.stringify(ruleDefinitionPayload));
      } else if (
        request.url.includes(
          'pim_enrich_locale_rest_index?%7B%22activated%22:true%7D'
        )
      ) {
        return Promise.resolve(JSON.stringify(locales));
      } else if (request.url.includes('pim_enrich_channel_rest_index')) {
        return Promise.resolve(JSON.stringify(scopesPayload));
      }

      throw new Error(`The "${request.url}" url is not mocked.`);
    });
    // When
    const {findByText, findByLabelText} = render(
      <EditRules
        ruleDefinitionCode={ruleDefinitionCode}
        setIsDirty={setIsDirty}
      />,
      {
        legacy: true,
      }
    );
    // Then
    expect(await findByText('My code')).toBeInTheDocument();
    expect(await findByLabelText('French (France)')).toBeInTheDocument();
    expect(
      await findByLabelText('English (United States)')
    ).toBeInTheDocument();
    expect(
      await screen.findByText('pimee_catalog_rule.form.edit.status.label')
    ).toBeInTheDocument();
    expect(
      await screen.findByText('pimee_catalog_rule.form.edit.status.enabled')
    ).toBeInTheDocument();
  });

  it('should add a Family Line', async () => {
    // Given
    fetchMock.mockResponse((request: Request) => {
      if (
        request.url.includes(
          'pimee_enrich_rule_definition_get?%7B%22ruleCode%22:%22my_code%22%7D'
        )
      ) {
        return Promise.resolve(JSON.stringify(ruleDefinitionPayload));
      } else if (
        request.url.includes(
          'pimee_enrich_rule_definition_get_available_fields'
        )
      ) {
        return Promise.resolve(JSON.stringify(addConditionFieldsPayload));
      } else if (request.url.includes('pim_enrich_channel_rest_index')) {
        return Promise.resolve(JSON.stringify(scopesPayload));
      } else if (
        request.url.includes(
          'pim_enrich_locale_rest_index?%7B%22activated%22:true%7D'
        )
      ) {
        return Promise.resolve(JSON.stringify(locales));
      }

      throw new Error(`The "${request.url}" url is not mocked.`);
    });

    // When
    const {findByLabelText, findByText, findByTestId} = render(
      <EditRules
        ruleDefinitionCode={ruleDefinitionCode}
        setIsDirty={setIsDirty}
      />,
      {
        legacy: true,
      }
    );
    // Then
    userEvent.click(
      await findByLabelText('pimee_catalog_rule.form.edit.add_conditions')
    );
    expect(
      (await findByLabelText('pimee_catalog_rule.form.edit.add_conditions'))
        .children.length
    ).toBeGreaterThan(1);
    fireEvent.change(
      await findByLabelText('pimee_catalog_rule.form.edit.add_conditions'),
      {
        target: {value: 'family'},
      }
    );
    expect(await findByText('Family')).toBeInTheDocument();
    expect((await findByTestId('condition-list')).children.length).toEqual(1);
  });

  it('should be able to change the status', async () => {
    // Given
    fetchMock.mockResponse((request: Request) => {
      if (
        request.url.includes(
          'pimee_enrich_rule_definition_get?%7B%22ruleCode%22:%22my_code%22%7D'
        )
      ) {
        return Promise.resolve(
          JSON.stringify({...ruleDefinitionPayload, enabled: false})
        );
      } else if (
        request.url.includes(
          'pim_enrich_locale_rest_index?%7B%22activated%22:true%7D'
        )
      ) {
        return Promise.resolve(JSON.stringify(locales));
      } else if (request.url.includes('pim_enrich_channel_rest_index')) {
        return Promise.resolve(JSON.stringify(scopesPayload));
      }

      throw new Error(`The "${request.url}" url is not mocked.`);
    });
    // When
    render(
      <EditRules
        ruleDefinitionCode={ruleDefinitionCode}
        setIsDirty={setIsDirty}
      />,
      {
        legacy: true,
      }
    );
    // Then
    expect(
      await screen.findByText('pimee_catalog_rule.form.edit.status.disabled')
    ).toBeInTheDocument();
    expect(
      screen.queryByText('pimee_catalog_rule.form.edit.status.enabled')
    ).not.toBeInTheDocument();

    await act(async () => {
      userEvent.click(await screen.findByTestId('edit-rules-input-status'));
    });
    expect(
      await screen.findByText('pimee_catalog_rule.form.edit.status.enabled')
    ).toBeInTheDocument();
    expect(
      screen.queryByText('pimee_catalog_rule.form.edit.status.disabled')
    ).not.toBeInTheDocument();
  });

  it('should add an Action Line', async () => {
    fetchMock.mockResponse((request: Request) => {
      if (
        request.url.includes(
          'pimee_enrich_rule_definition_get?%7B%22ruleCode%22:%22my_code%22%7D'
        )
      ) {
        return Promise.resolve(JSON.stringify(ruleDefinitionPayload));
      } else if (
        request.url.includes(
          'pim_enrich_locale_rest_index?%7B%22activated%22:true%7D'
        )
      ) {
        return Promise.resolve(JSON.stringify(locales));
      } else if (request.url.includes('pim_enrich_channel_rest_index')) {
        return Promise.resolve(JSON.stringify(scopesPayload));
      }

      throw new Error(`The "${request.url}" url is not mocked.`);
    });

    // When
    const {findByLabelText} = render(
      <EditRules
        ruleDefinitionCode={ruleDefinitionCode}
        setIsDirty={setIsDirty}
      />,
      {
        legacy: true,
      }
    );
    // Then
    userEvent.click(
      await findByLabelText('pimee_catalog_rule.form.edit.actions.add_action')
    );
    expect(
      (await findByLabelText('pimee_catalog_rule.form.edit.actions.add_action'))
        .children.length
    ).toBeGreaterThan(1);
    fireEvent.change(
      await findByLabelText('pimee_catalog_rule.form.edit.actions.add_action'),
      {
        target: {value: 'set_family'},
      }
    );
    await waitForElementToBeRemoved(() =>
      document.querySelector(
        'div[data-testid="action-list"] img[alt="pim_common.loading"]'
      )
    ).then(() => {
      expect(screen.getByTestId('action-list').children.length).toEqual(3);
    });
  });

  it('should render a 404 error', async () => {
    // Given
    fetchMock.mockResponse((request: Request) => {
      if (
        request.url.includes(
          'pimee_enrich_rule_definition_get?%7B%22ruleCode%22:%22inexisting_rule%22%7D'
        )
      ) {
        return Promise.resolve({status: 404});
      } else if (
        request.url.includes(
          'pim_enrich_locale_rest_index?%7B%22activated%22:true%7D'
        )
      ) {
        return Promise.resolve(JSON.stringify(locales));
      } else if (request.url.includes('pim_enrich_channel_rest_index')) {
        return Promise.resolve(JSON.stringify(scopesPayload));
      }

      throw new Error(`The "${request.url}" url is not mocked.`);
    });
    // When
    const {findByText} = render(
      <EditRules
        ruleDefinitionCode='inexisting_rule'
        setIsDirty={setIsDirty}
      />,
      {
        legacy: true,
      }
    );
    // Then
    expect(await findByText('404')).toBeInTheDocument();
  });

  it('should render a fallback error', async () => {
    // Given
    fetchMock.mockResponse((request: Request) => {
      if (
        request.url.includes(
          'pimee_enrich_rule_definition_get?%7B%22ruleCode%22:%22malformed_rule%22%7D'
        )
      ) {
        return Promise.resolve(JSON.stringify({foo: 'bar'}));
      } else if (
        request.url.includes(
          'pim_enrich_locale_rest_index?%7B%22activated%22:true%7D'
        )
      ) {
        return Promise.resolve(JSON.stringify(locales));
      } else if (request.url.includes('pim_enrich_channel_rest_index')) {
        return Promise.resolve(JSON.stringify(scopesPayload));
      }

      throw new Error(`The "${request.url}" url is not mocked.`);
    });

    // When
    const {findByText} = render(
      <EditRules ruleDefinitionCode='malformed_rule' setIsDirty={setIsDirty} />,
      {
        legacy: true,
      }
    );
    // Then
    expect(await findByText('500')).toBeInTheDocument();
  });
  it('should render a non authorized page', async () => {
    dependencies.security = {
      isGranted: jest
        .fn((_acl: string) => true)
        .mockImplementationOnce((_acl: string) => false),
    };
    // When
    render(
      <EditRules
        ruleDefinitionCode={ruleDefinitionCode}
        setIsDirty={setIsDirty}
      />,
      {
        legacy: true,
      }
    );
    // Then
    expect(await screen.findByText('error.exception'));
    expect(screen.getByText('401'));
  });
});
