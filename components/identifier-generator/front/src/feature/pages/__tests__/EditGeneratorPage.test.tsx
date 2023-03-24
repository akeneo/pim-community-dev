import React from 'react';
import {mockResponse, render, screen, waitFor} from '../../tests/test-utils';
import {EditGeneratorPage} from '../';
import userEvent from '@testing-library/user-event';
import {NotificationLevel} from '@akeneo-pim-community/shared';
import initialGenerator from '../../tests/fixtures/initialGenerator';
import {act, fireEvent} from '@testing-library/react';

const mockNotify = jest.fn();

const userContext = {
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

jest.mock('@akeneo-pim-community/shared', () => ({
  ...jest.requireActual('@akeneo-pim-community/shared'),
  useTranslate: () => (key: string) => key,
  useRouter: () => {
    return {
      generate: (key: string) => key,
    };
  },
  useNotify: () => {
    return mockNotify;
  },
  useUserContext: () => {
    return userContext;
  },
  useSecurity: () => {
    return {
      isGranted: () => true,
    };
  },
}));

const defaultUiLocales = [
  {
    id: 42,
    code: 'en_US',
    label: 'English (United States)',
    region: 'United States',
    language: 'English',
  },
  {
    id: 69,
    code: 'fr_FR',
    label: 'French (France)',
    region: 'France',
    language: 'French',
  },
  {
    id: 96,
    code: 'de_DE',
    label: 'German (Germany)',
    region: 'Germany',
    language: 'German',
  },
];

describe('EditGeneratorPage', () => {
  beforeEach(() => {
    mockResponse('pim_localization_locale_index', 'GET', {json: defaultUiLocales});
  });
  it('should render page', () => {
    render(<EditGeneratorPage initialGenerator={initialGenerator} />);
    expect(screen.getByText('pim_common.save')).toBeInTheDocument();
  });

  it('should save generator and show toast', async () => {
    mockResponse('akeneo_identifier_generator_rest_update', 'PATCH', {
      ok: true,
      json: () => Promise.resolve(initialGenerator),
    });

    render(<EditGeneratorPage initialGenerator={initialGenerator} />);
    expect(screen.getByText('pim_common.save')).toBeInTheDocument();

    userEvent.click(screen.getByText('pim_common.save'));

    await waitFor(() => {
      expect(fetch).toHaveBeenCalled();
    });

    expect(mockNotify).toHaveBeenCalled();
    expect(mockNotify).toHaveBeenCalledWith(NotificationLevel.SUCCESS, 'pim_identifier_generator.flash.update.success');
  });

  it('should save generator with error and show toast', async () => {
    mockResponse('akeneo_identifier_generator_rest_update', 'PATCH', {
      ok: false,
      json: [
        {
          message: 'Association type code may contain only letters, numbers and underscores',
          path: 'code',
        },
      ],
    });

    render(<EditGeneratorPage initialGenerator={initialGenerator} />);
    expect(screen.getByText('pim_common.save')).toBeInTheDocument();

    userEvent.click(screen.getByText('pim_common.save'));

    await waitFor(() => {
      expect(fetch).toHaveBeenCalled();
    });

    expect(mockNotify).toHaveBeenCalled();
    expect(mockNotify).toHaveBeenCalledWith(NotificationLevel.ERROR, 'pim_identifier_generator.flash.create.error');
  });

  it('should check generator validation on save', () => {
    render(<EditGeneratorPage initialGenerator={{...initialGenerator, structure: []}} />);
    expect(screen.getByText('pim_common.save')).toBeInTheDocument();

    act(() => {
      fireEvent.click(screen.getByText('pim_common.save'));
    });

    expect(mockNotify).not.toHaveBeenCalled();
    // we should see a red pill to know there is an error
    expect(screen.getByRole('alert')).toBeInTheDocument();
  });
});
