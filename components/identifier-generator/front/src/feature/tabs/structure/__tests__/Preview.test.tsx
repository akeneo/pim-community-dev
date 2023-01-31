import React from 'react';
import {render, screen} from '../../../tests/test-utils';
import {PROPERTY_NAMES, Structure, TEXT_TRANSFORMATION} from '../../../models';
import {Preview} from '../Preview';

describe('Preview', () => {
  it('displays the preview', () => {
    const structure: Structure = [
      {type: PROPERTY_NAMES.FREE_TEXT, string: 'AKN'},
      {type: PROPERTY_NAMES.FREE_TEXT, string: '42'},
    ];
    render(<Preview structure={structure} delimiter={'-'} textTransformation={TEXT_TRANSFORMATION.NO} />);

    expect(screen.getByText('AKN')).toBeInTheDocument();
    expect(screen.getByText('-')).toBeInTheDocument();
    expect(screen.getByText('42')).toBeInTheDocument();
  });

  it('displays the lowercase preview', () => {
    const structure: Structure = [
      {type: PROPERTY_NAMES.FREE_TEXT, string: 'AkN'},
      {type: PROPERTY_NAMES.FREE_TEXT, string: '42'},
    ];
    render(<Preview structure={structure} delimiter={'fOo'} textTransformation={TEXT_TRANSFORMATION.LOWERCASE} />);

    expect(screen.getByText('akn')).toBeInTheDocument();
    expect(screen.getByText('foo')).toBeInTheDocument();
    expect(screen.getByText('42')).toBeInTheDocument();
  });

  it('displays the uppercase preview', () => {
    const structure: Structure = [
      {type: PROPERTY_NAMES.FREE_TEXT, string: 'AkN'},
      {type: PROPERTY_NAMES.FREE_TEXT, string: '42'},
    ];
    render(<Preview structure={structure} delimiter={'fOo'} textTransformation={TEXT_TRANSFORMATION.UPPERCASE} />);

    expect(screen.getByText('AKN')).toBeInTheDocument();
    expect(screen.getByText('FOO')).toBeInTheDocument();
    expect(screen.getByText('42')).toBeInTheDocument();
  });
});
