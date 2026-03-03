import React from 'react';
import {fireEvent, mockACLs, mockResponse, render, screen, waitFor} from '../../tests/test-utils';
import {LabelTranslations} from '../LabelTranslations';

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
  it('should make the labels readonly without ACL', async () => {
    mockACLs(true, false);
    mockResponse('pim_localization_locale_index', 'GET', {json: defaultUiLocales});

    const onLabelsChange = jest.fn();
    render(<LabelTranslations labelCollection={labelCollection} onLabelsChange={onLabelsChange} />);

    await waitFor(() => screen.getByText('English (United States)'));
    fireEvent.change(screen.getByTitle(''), {target: {value: 'German Label'}});
    expect(onLabelsChange).not.toBeCalledWith();
  });

  it('should render the initial values', async () => {
    mockResponse('pim_localization_locale_index', 'GET', {json: defaultUiLocales});

    const onLabelsChange = jest.fn();
    render(<LabelTranslations labelCollection={labelCollection} onLabelsChange={onLabelsChange} />);

    expect(screen.getByText('pim_identifier_generator.general.label_translations_in_ui_locale')).toBeInTheDocument();
    await waitFor(() => screen.getByText('English (United States)'));
    expect(screen.getByText('French (France)')).toBeInTheDocument();
    expect(screen.getByText('German (Germany)')).toBeInTheDocument();

    expect(screen.getByTitle('English Label')).toBeInTheDocument();
    expect(screen.getByTitle('French Label')).toBeInTheDocument();
    expect(screen.getByTitle('')).toBeInTheDocument();
  });

  it('should update label', async () => {
    mockResponse('pim_localization_locale_index', 'GET', {json: defaultUiLocales});

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
    mockResponse('pim_localization_locale_index', 'GET', {json: [], ok: false});

    const onLabelsChange = jest.fn();
    render(<LabelTranslations labelCollection={labelCollection} onLabelsChange={onLabelsChange} />);
    expect(await screen.findByText('pim_error.general')).toBeInTheDocument();
  });

  it('should update not-empty labels', async () => {
    mockResponse('pim_localization_locale_index', 'GET', {json: defaultUiLocales});

    const onLabelsChange = jest.fn();
    render(<LabelTranslations labelCollection={labelCollection} onLabelsChange={onLabelsChange} />);

    await waitFor(() => screen.getByTitle('French Label'));
    fireEvent.change(screen.getByTitle('French Label'), {target: {value: '   '}});
    expect(onLabelsChange).toBeCalledWith({
      en_US: 'English Label',
    });
  });
});
