import React from 'react';
import {fireEvent, render, screen} from '../../../tests/test-utils';
import {IdentifierGenerator} from '../../../../models';
import {GeneralProperties} from '../GeneralProperties';

jest.mock('@akeneo-pim-community/shared', () => ({
  ...jest.requireActual('@akeneo-pim-community/shared'),
  useTranslate: () => (key: string) => key,
}));
jest.mock('../LabelTranslations');

describe('GeneralProperties', () => {
  it('should render the code input', () => {
    const generator: IdentifierGenerator = {
      code: 'initialCode',
      labels: {},
    };
    const onGeneratorChange = jest.fn();
    render(<GeneralProperties generator={generator} onGeneratorChange={onGeneratorChange} />);
    expect(screen.getByText('pim_identifier_generator.general.title')).toBeInTheDocument();
    expect(screen.getByText('pim_common.code')).toBeInTheDocument();
    expect(screen.getByTitle('initialCode')).toBeInTheDocument();
  });

  it('should update labels', () => {
    const generator: IdentifierGenerator = {
      code: 'initialCode',
      labels: {},
    };
    const onGeneratorChange = jest.fn();
    render(<GeneralProperties generator={generator} onGeneratorChange={onGeneratorChange} />);
    expect(screen.getByText('LabelTranslationsMock')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Update French Label'));
    expect(onGeneratorChange).toBeCalledWith({
      code: 'initialCode',
      labels: {
        fr_FR: 'FrenchUpdated',
      },
    });
  });
});
