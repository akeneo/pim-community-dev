import React from 'react';
import {render, screen} from '../../tests/test-utils';
import {CreateGeneratorPage} from '../';
import {IdentifierGenerator} from '../../models';
import {Router} from 'react-router-dom';
import {act, fireEvent, waitFor} from '@testing-library/react';
import {createMemoryHistory} from 'history';

jest.mock('../CreateOrEditGeneratorPage');

const initialGenerator: IdentifierGenerator = {
  code: 'initialCode',
  labels: {
    en_US: 'Initial Label',
  },
  conditions: [],
  structure: [],
  delimiter: null,
  target: 'sku',
};

describe('CreateGeneratorPage', () => {
  it('should create a generator', async () => {
    const history = createMemoryHistory();
    render(
      <Router history={history}>
        <CreateGeneratorPage initialGenerator={initialGenerator} />
      </Router>
    );
    expect(screen.getByText('CreateOrEditGeneratorPage')).toBeInTheDocument();

    jest.spyOn(global, 'fetch').mockResolvedValue({
      status: 201,
    } as Response);

    act(() => {
      fireEvent.click(screen.getByText('Main button'));
    });

    await waitFor(() => history.length > 1);
    expect(history.location.pathname).toBe('/initialCode');
  });

  it('should display validation errors', async () => {
    const history = createMemoryHistory();
    const violationErrors = [{message: 'a message', path: 'a path'}, {message: 'another message'}];

    jest.spyOn(global, 'fetch').mockImplementation(input => {
      if (input === 'akeneo_identifier_generator_rest_create') {
        return Promise.resolve({
          status: 400,
          json: () => Promise.resolve(violationErrors),
        } as Response);
      }
      return Promise.resolve({
        json: () => Promise.resolve([]),
      } as Response);
    });

    render(
      <Router history={history}>
        <CreateGeneratorPage initialGenerator={initialGenerator} />
      </Router>
    );
    expect(screen.getByText('CreateOrEditGeneratorPage')).toBeInTheDocument();

    act(() => {
      fireEvent.click(screen.getByText('Main button'));
    });

    expect(await screen.findByText('a path a message')).toBeInTheDocument();
    expect(await screen.findByText('another message')).toBeInTheDocument();
  });
});
