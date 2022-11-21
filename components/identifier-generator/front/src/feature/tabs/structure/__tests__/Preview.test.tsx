import React from 'react';
import {render, screen} from '../../../tests/test-utils';
import {PROPERTY_NAMES, Structure} from '../../../models';
import {Preview} from '../Preview';

describe('Preview', () => {
  it('displays the preview', () => {
    const structure: Structure = [
      {type: PROPERTY_NAMES.FREE_TEXT, string: 'AKN'},
      {type: PROPERTY_NAMES.FREE_TEXT, string: '42'},
    ];
    render(<Preview structure={structure} delimiter={'-'} />);

    expect(screen.getByText('AKN')).toBeInTheDocument();
    expect(screen.getByText('-')).toBeInTheDocument();
    expect(screen.getByText('42')).toBeInTheDocument();
  });
});
