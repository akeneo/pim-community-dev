import React from 'react';
import {fireEvent, render, screen} from '../../tests/test-utils';
import {IdentifierGenerator, TEXT_TRANSFORMATION} from '../../models';
import {GeneralPropertiesTab} from '../GeneralPropertiesTab';

jest.mock('../../components/LabelTranslations');
jest.mock('../../components/IdentifierAttributeSelector');

describe('GeneralProperties', () => {
  it('should render the code input', () => {
    const generator: IdentifierGenerator = {
      code: 'initialCode',
      target: 'sku',
      structure: [],
      conditions: [],
      labels: {},
      delimiter: null,
      text_transformation: TEXT_TRANSFORMATION.NO,
    };
    const onGeneratorChange = jest.fn();
    render(<GeneralPropertiesTab generator={generator} onGeneratorChange={onGeneratorChange} validationErrors={[]} />);
    expect(screen.getByText('pim_identifier_generator.general.title')).toBeInTheDocument();
    expect(screen.getByText('pim_common.code')).toBeInTheDocument();
    expect(screen.getByTitle('initialCode')).toBeInTheDocument();
  });

  it('should update labels', () => {
    const generator: IdentifierGenerator = {
      code: 'initialCode',
      target: 'sku',
      structure: [],
      conditions: [],
      labels: {},
      delimiter: null,
      text_transformation: TEXT_TRANSFORMATION.NO,
    };
    const onGeneratorChange = jest.fn();
    render(<GeneralPropertiesTab generator={generator} onGeneratorChange={onGeneratorChange} validationErrors={[]} />);
    expect(screen.getByText('LabelTranslationsMock')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Update French Label'));
    expect(onGeneratorChange).toBeCalledWith({
      code: 'initialCode',
      target: 'sku',
      structure: [],
      conditions: [],
      labels: {
        fr_FR: 'FrenchUpdated',
      },
      delimiter: null,
      text_transformation: TEXT_TRANSFORMATION.NO,
    });
  });

  it('should update text transformation', () => {
    const generator: IdentifierGenerator = {
      code: 'initialCode',
      target: 'sku',
      structure: [],
      conditions: [],
      labels: {},
      delimiter: null,
      text_transformation: TEXT_TRANSFORMATION.NO,
    };
    const onGeneratorChange = jest.fn();
    render(<GeneralPropertiesTab generator={generator} onGeneratorChange={onGeneratorChange} validationErrors={[]} />);
    expect(screen.getByText('pim_identifier_generator.general.text_transformation.label')).toBeInTheDocument();
    expect(screen.getByText('pim_identifier_generator.general.text_transformation.no')).toBeInTheDocument();
    fireEvent.click(screen.getByTitle('pim_common.open'));
    expect(screen.getByText('pim_identifier_generator.general.text_transformation.uppercase')).toBeInTheDocument();
    expect(screen.getByText('pim_identifier_generator.general.text_transformation.lowercase')).toBeInTheDocument();
    fireEvent.click(screen.getByText('pim_identifier_generator.general.text_transformation.uppercase'));
    expect(onGeneratorChange).toBeCalledWith({
      code: 'initialCode',
      target: 'sku',
      structure: [],
      conditions: [],
      labels: {},
      delimiter: null,
      text_transformation: 'uppercase',
    });
  });

  it('should update target', () => {
    const generator: IdentifierGenerator = {
      code: 'initialCode',
      target: 'sku',
      structure: [],
      conditions: [],
      labels: {},
      delimiter: null,
      text_transformation: TEXT_TRANSFORMATION.NO,
    };
    const onGeneratorChange = jest.fn();
    render(<GeneralPropertiesTab generator={generator} onGeneratorChange={onGeneratorChange} validationErrors={[]} />);
    expect(screen.getByText('IdentifierAttributeSelectorMock')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Change target'));
    expect(onGeneratorChange).toBeCalledWith({
      code: 'initialCode',
      target: 'ean',
      structure: [],
      conditions: [],
      labels: {},
      delimiter: null,
      text_transformation: TEXT_TRANSFORMATION.NO,
    });
  });

  it('should show displayed errors', () => {
    const generator: IdentifierGenerator = {
      code: 'initialCode',
      target: 'sku',
      structure: [],
      conditions: [],
      labels: {},
      delimiter: null,
      text_transformation: TEXT_TRANSFORMATION.NO,
    };
    const onGeneratorChange = jest.fn();
    const validationErrors = [{path: 'labels', message: 'error on a label'}];
    render(
      <GeneralPropertiesTab
        generator={generator}
        onGeneratorChange={onGeneratorChange}
        validationErrors={validationErrors}
      />
    );

    expect(screen.getByText('error on a label')).toBeInTheDocument();
  });
});
