import React from 'react';
import {FamiliesSelector} from '../';
import {mockResponse, render, screen} from '../../tests/test-utils';

describe('FamiliesSelector', () => {
  it('should render the family labels from family codes', async () => {
    const expectCall = mockResponse('akeneo_identifier_generator_get_families', 'GET', {
      ok: true,
      json: [
        {code: 'family1', labels: {en_US: 'Family1 label'}},
        {code: 'family2', labels: {}},
      ],
    });

    render(<FamiliesSelector onChange={jest.fn()} familyCodes={['family1', 'family2']} />);

    expect(await screen.findByText('Family1 label')).toBeInTheDocument();
    expect(await screen.findByText('[family2]')).toBeInTheDocument();

    expectCall();
  });

  it('should render unauthorized error', async () => {
    mockResponse('akeneo_identifier_generator_get_families', 'GET', {
      ok: false,
      status: 403,
    });

    render(<FamiliesSelector onChange={jest.fn()} familyCodes={['family1', 'family2']} />);

    expect(await screen.findByText('pim_error.unauthorized_list_families')).toBeInTheDocument();
  });

  it('should render default error', async () => {
    mockResponse('akeneo_identifier_generator_get_families', 'GET', {
      ok: false,
      status: 500,
    });

    render(<FamiliesSelector onChange={jest.fn()} familyCodes={['family1', 'family2']} />);

    expect(await screen.findByText('pim_error.general')).toBeInTheDocument();
  });
});
