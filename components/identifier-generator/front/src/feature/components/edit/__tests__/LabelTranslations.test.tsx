import React from 'react';
import {fireEvent, render, screen, waitFor} from '../../../tests/test-utils';
import {LabelTranslations} from '../LabelTranslations';
import {setLogger} from 'react-query';

jest.mock('@akeneo-pim-community/shared', () => ({
  ...jest.requireActual('@akeneo-pim-community/shared'),
  useTranslate: () => (key: string) => key,
}));

setLogger({
  // eslint-disable-next-line no-console
  log: console.log,
  // eslint-disable-next-line no-console
  warn: console.warn,
  // no more errors on the console
  // eslint-disable-next-line @typescript-eslint/no-empty-function
  error: () => {},
});

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

const labelCollection = {
  en_US: 'English Label',
  fr_FR: 'French Label',
};

describe('LabelTranslations', () => {
  it('should render the initial values', async () => {
    // @ts-ignore
    jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: true,
      json: () => Promise.resolve(defaultUiLocales),
    });

    const onLabelsChange = jest.fn();
    render(<LabelTranslations labelCollection={labelCollection} onLabelsChange={onLabelsChange} />);

    expect(screen.getByText('pim_identifier_generator.general.label_translations_in_ui_locale')).toBeInTheDocument();
    await waitFor(() => screen.getByText('English (United States)'));
    // expect(screen.getByText('English (United States)')).toBeInTheDocument();
    expect(screen.getByText('French (France)')).toBeInTheDocument();
    expect(screen.getByText('German (Germany)')).toBeInTheDocument();

    expect(screen.getByTitle('English Label')).toBeInTheDocument();
    expect(screen.getByTitle('French Label')).toBeInTheDocument();
    expect(screen.getByTitle('')).toBeInTheDocument();
  });

  it('should update label', async () => {
    // @ts-ignore
    jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: true,
      json: () => Promise.resolve(defaultUiLocales),
    });

    const onLabelsChange = jest.fn();
    render(<LabelTranslations labelCollection={labelCollection} onLabelsChange={onLabelsChange} />);

    await waitFor(() => screen.getByText('English (United States)'));
    fireEvent.change(screen.getByTitle(''), {target: {value: 'German Label'}});
    expect(onLabelsChange).toBeCalledWith({
      de_DE: 'German Label',
      en_US: 'English Label',
      fr_FR: 'French Label',
    });
  });

  it('should display an error if impossible to fetch locales', async () => {
    // @ts-ignore
    jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: false,
      statusText: 'unexpected error',
      json: () => Promise.resolve([]),
    });
    const onLabelsChange = jest.fn();

    render(<LabelTranslations labelCollection={labelCollection} onLabelsChange={onLabelsChange} />);
    expect(await screen.findByText('pim_error.general')).toBeInTheDocument();
  });
});
