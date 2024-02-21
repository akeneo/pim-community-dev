import React from 'react';
import {Illustration} from '.';
import {renderWithProviders} from '../../../tests/utils';

describe('Page Header Illustration', () => {
  test('it renders illustration with alt title', () => {
    const {queryByAltText} = renderWithProviders(
      <Illustration
        src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=="
        title="DUMMY_TITLE"
      />
    );

    expect(queryByAltText('DUMMY_TITLE')).toBeInTheDocument();
  });

  test('it renders illustration with empty alt title', () => {
    const {queryByAltText} = renderWithProviders(
      <Illustration src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" />
    );

    expect(queryByAltText('')).toBeInTheDocument();
  });
});
