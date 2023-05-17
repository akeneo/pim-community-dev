import React from 'react';
import {fireEvent, mockACLs, render, screen, waitFor} from '../../tests/test-utils';
import {LabelTranslations} from '../LabelTranslations';
import {server} from '../../mocks/server';
import {rest} from 'msw';
import uiLocales from '../../tests/fixtures/uiLocales';

const labelCollection = {
  en_US: 'English Label',
  fr_FR: 'French Label',
};

describe('LabelTranslations', () => {
  it('should make the labels readonly without ACL', async () => {
    mockACLs(true, false);

    const onLabelsChange = jest.fn();
    render(<LabelTranslations labelCollection={labelCollection} onLabelsChange={onLabelsChange} />);

    await waitFor(() => screen.getByText('English (United States)'));
    fireEvent.change(screen.getAllByTitle('')[0], {target: {value: 'German Label'}});
    expect(onLabelsChange).not.toBeCalledWith();
  });

  it('should render the initial values', async () => {
    const onLabelsChange = jest.fn();
    render(<LabelTranslations labelCollection={labelCollection} onLabelsChange={onLabelsChange} />);

    expect(screen.getByText('pim_identifier_generator.general.label_translations_in_ui_locale')).toBeInTheDocument();
    await waitFor(() => screen.getByText('English (United States)'));
    expect(screen.getByText('French (France)')).toBeInTheDocument();
    expect(screen.getByText('German (Germany)')).toBeInTheDocument();

    expect(screen.getByTitle('English Label')).toBeInTheDocument();
    expect(screen.getByTitle('French Label')).toBeInTheDocument();
    expect(screen.getAllByTitle('')[0]).toBeInTheDocument();
  });

  it('should update label', async () => {
    const onLabelsChange = jest.fn();
    render(<LabelTranslations labelCollection={labelCollection} onLabelsChange={onLabelsChange} />);

    await waitFor(() => screen.getByText('English (United States)'));
    fireEvent.change(screen.getAllByTitle('')[0], {target: {value: 'German Label'}});
    expect(onLabelsChange).toBeCalledWith({
      ca_ES: 'German Label',
      en_US: 'English Label',
      fr_FR: 'French Label',
    });
  });

  it('should display an error if impossible to fetch locales', async () => {
    server.use(
      rest.get('/pim_localization_locale_index', (req, res, ctx) => {
        return res(ctx.status(500), ctx.json(uiLocales));
      })
    );
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
