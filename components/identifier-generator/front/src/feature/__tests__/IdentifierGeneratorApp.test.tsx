import React from 'react';
import {IdentifierGeneratorApp} from '../';
import {act, fireEvent, render, screen} from '../tests/test-utils';
import {createHashHistory} from 'history';
import {Router} from 'react-router-dom';

describe('IdentifierGeneratorApp', () => {
  it('is just an example of unit test', () => {
    const history = createHashHistory();
    history.push('/configuration/identifier-generator/');
    render(
      <Router history={history}>
        <IdentifierGeneratorApp />
      </Router>
    );

    expect(screen.getAllByText('pim_title.akeneo_identifier_generator_index')).toHaveLength(2);
    act(() => {
      fireEvent.click(screen.getByText('pim_common.create'));
    });
  });
});
