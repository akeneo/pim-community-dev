import React from 'react';
import {mockResponse, render} from '../../tests/test-utils';
import {SimpleSelectOptionsSelector} from '../SimpleSelectOptionsSelector';
import {fireEvent, waitFor} from '@testing-library/react';
import {screen} from 'akeneo-design-system/lib/storybook/test-util';
import {FamiliesSelector} from '../FamiliesSelector';

jest.mock('@akeneo-pim-community/shared', () => ({
  ...jest.requireActual('@akeneo-pim-community/shared'),
  useTranslate: () => (i18nKey: string) => {
    return i18nKey;
  },
  useRouter: () => {
    return {
      generate: (key: string, params) => ({key, params}),
    };
  },
  useNotify: () => {
    return () => {};
  },
  useUserContext: () => {
    return {
      get: (k: string) => {
        switch (k) {
          case 'catalogLocale':
            return 'en_US';
          case 'uiLocale':
            return 'en_US';
          default:
            throw new Error(`Unknown key ${k}`);
        }
      },
    };
  },
}));

const selectedOptionsMockResponse = [
  {'code':'option_a', 'labels':{'en_US':'Option A'}},
  {'code':'last_option', 'labels':{'en_US':'[last_option]'}}
];

const firstPaginatedResponse = [
  {'code':'option_a', 'labels':{'en_US':'OptionA'}},
  {'code':'option_b', 'labels':{'en_US':'OptionB'}},
  {'code':'option_c', 'labels':{'en_US':'OptionC'}},
  {'code':'option_d', 'labels':{'en_US':'OptionD'}},
  {'code':'option_e', 'labels':{'en_US':'OptionE'}},
  {'code':'option_n', 'labels':{'en_US':'OptionN'}},
  {'code':'option_v', 'labels':{'en_US':'OptionV'}},
  {'code':'option_x', 'labels':{'en_US':'OptionX'}},
  {'code':'option_w', 'labels':{'en_US':'OptionW'}},
  {'code':'option_q', 'labels':{'en_US':'OptionQ'}},
  {'code':'option_s', 'labels':{'en_US':'OptionS'}},
  {'code':'option_l', 'labels':{'en_US':'OptionL'}},
  {'code':'option_m', 'labels':{'en_US':'OptionM'}},
  {'code':'option_o', 'labels':{'en_US':'OptionO'}},
  {'code':'option_u', 'labels':{'en_US':'OptionU'}},
  {'code':'option_1', 'labels':{'en_US':'Option1'}},
  {'code':'option_2', 'labels':{'en_US':'Option2'}},
  {'code':'option_3', 'labels':{'en_US':'Option3'}},
  {'code':'option_4', 'labels':{'en_US':'Option4'}},
  {'code':'option_5', 'labels':{'en_US':'Option5'}},
  {'code':'option_6', 'labels':{'en_US':'Option6'}},
];
const secondPaginatedResponse = [
  {'code':'option_f', 'labels':{'en_US':'OptionF'}},
  {'code':'option_g', 'labels':{'en_US':'OptionG'}},
  {'code':'option_h', 'labels':{'en_US':'OptionH'}},
  {'code':'option_i', 'labels':{'en_US':'OptionI'}},
  {'code':'option_j', 'labels':{'en_US':'OptionJ'}}
];

describe('SimpleSelectOptionsSelector', () => {
  it('should show selected values in multi select with any values', async () => {
    mockGetOptionCodes({
      ok: true,
      json: firstPaginatedResponse,
    });

    const screen = render(
      <SimpleSelectOptionsSelector
        attributeCode={'brand'}
        optionCodes={['option_a', 'invalid_code', 'last_option']}
        onChange={jest.fn()}
      />
    );

    await waitFor(() => {
      expect(screen.getByText('OptionA')).toBeInTheDocument();
    });
    expect(screen.getByText('invalid_code')).toBeInTheDocument();
    expect(screen.getByText('[last_option]')).toBeInTheDocument();
  });

  it('should search for options by label and select them', async () => {
    const fetchImplementation = mockGetOptionCodes({
      ok: true,
      json: []
    });
    const mockedOnChange = jest.fn();

    const screen = render(
      <SimpleSelectOptionsSelector
        attributeCode={'brand'}
        optionCodes={['option_a', 'invalid_code', 'last_option']}
        onChange={mockedOnChange}
      />
    );

    await waitFor(() => {
      expect(screen.getByText('OptionA')).toBeInTheDocument();
    });

    const input = screen.getByRole('textbox');
    fireEvent.click(input);
    fireEvent.change(input, {target: {value: 'OptionF'}});

    expect(fetchImplementation).toHaveBeenCalledTimes(3);

    await waitFor(() => {
      const frenchOption = screen.getByText('OptionF');
      expect(frenchOption).toBeInTheDocument();
    });

    const germanOption = screen.queryByText('OptionB');
    expect(germanOption).not.toBeInTheDocument();

    fireEvent.click(screen.getByText('OptionF'));
    expect(mockedOnChange).toHaveBeenCalled();
    expect(fetchImplementation).toHaveBeenCalledTimes(4);

    expect(screen.getByText('OptionB')).toBeInTheDocument();
    fireEvent.click(screen.getByText('OptionB'));
    expect(mockedOnChange).toHaveBeenCalled();
  });
});

it('should render unauthorized error', async () => {
  mockGetOptionCodes({
    ok: false,
    status: 403,
  });

  render(<SimpleSelectOptionsSelector
    attributeCode={'brand'}
    optionCodes={['option_a', 'invalid_code', 'last_option']}
    onChange={jest.fn()} />
  );

  expect(await screen.findByText('pim_error.unauthorized_list_families')).toBeInTheDocument();
});

it('should render default error', async () => {
  mockGetOptionCodes({
    ok: false,
    status: 500,
  });

  render(<SimpleSelectOptionsSelector
    attributeCode={'brand'}
    optionCodes={['option_a', 'invalid_code', 'last_option']}
    onChange={jest.fn()} />
  );

  expect(await screen.findByText('pim_error.general')).toBeInTheDocument();
});


const mockGetOptionCodes = (
  response: {
    ok?: boolean;
    json?: unknown;
    statusText?: string;
    status?: number;
    body?: unknown
  }) => {
  const fetchImplementation = jest.fn().mockImplementation((requestArgs: { key: string, params: any }, args) => {
    const resolvedPromise = {
          ok: response.ok,
          json: () => Promise.resolve(firstPaginatedResponse),
          statusText: response.statusText || '',
          status: response.status ?? 200,
    };
    if (!response.ok) {
      jest.spyOn(console, 'error');
      // eslint-disable-next-line no-console
      (console.error as jest.Mock).mockImplementation(() => null);
    }

    if (requestArgs.key !== 'akeneo_identifier_generator_get_attribute_options') {
      throw new Error(`Unmocked url "${requestArgs.key}" [${args.method}]`);
    }

    if (requestArgs.params.codes.length === 3 && requestArgs.params.limit === 3) {
      resolvedPromise.json = () => Promise.resolve(selectedOptionsMockResponse);
    } else if (requestArgs.params.page === 2) {
      resolvedPromise.json = () => Promise.resolve(secondPaginatedResponse);
    } else if (requestArgs.params.search === 'OptionF') {
      resolvedPromise.json = () => Promise.resolve([{'code':'option_f', 'labels':{'en_US':'OptionF'}},]);
    } else {
      resolvedPromise.json = () => Promise.resolve(firstPaginatedResponse);
    }

    return Promise.resolve(resolvedPromise);
  });
  jest.spyOn(global, 'fetch').mockImplementation(fetchImplementation);

  return fetchImplementation;
};
