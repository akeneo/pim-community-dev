import React, {useState} from 'react';
import styled from 'styled-components';
import {Collapse, Field, Helper, Pill, SelectInput} from 'akeneo-design-system';
import {
  filterErrors,
  getAllLocalesFromChannels,
  getLabel,
  Section,
  useTranslate,
  useUserContext,
  ValidationError,
} from '@akeneo-pim-community/shared';
import {useChannels, useReferenceEntityAttributes} from '../../../hooks';
import {isDefaultReferenceEntitySelection, ReferenceEntitySelection} from './model';
import {AttributeSelector} from './Attribute';

const AttributeSelectorContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: 5px;
`;

type ReferenceEntitySelectorProps = {
  referenceEntityCode: string;
  selection: ReferenceEntitySelection;
  validationErrors: ValidationError[];
  onSelectionChange: (updatedSelection: ReferenceEntitySelection) => void;
};

const ReferenceEntitySelector = ({
  selection,
  referenceEntityCode,
  validationErrors,
  onSelectionChange,
}: ReferenceEntitySelectorProps) => {
  const [isSelectorCollapsed, toggleSelectorCollapse] = useState<boolean>(false);
  const translate = useTranslate();
  const channels = useChannels();
  const referenceEntityAttributes = useReferenceEntityAttributes(referenceEntityCode);
  const catalogLocale = useUserContext().get('catalogLocale');
  const locales = getAllLocalesFromChannels(channels);
  const typeErrors = filterErrors(validationErrors, '[type]');

  const handleTypeChange = (type: string) => {
    if ('code' === type) {
      onSelectionChange({type: 'code'});
    } else {
      const referenceEntityAttribute = referenceEntityAttributes.find(({identifier}) => identifier === type);

      if (undefined !== referenceEntityAttribute) {
        onSelectionChange({
          type: 'attribute',
          attribute_identifier: type,
          attribute_type: referenceEntityAttribute.type,
          reference_entity_code: referenceEntityCode,
          channel: referenceEntityAttribute.value_per_channel ? channels[0].code : null,
          locale: referenceEntityAttribute.value_per_locale ? locales[0].code : null,
        });
      }
    }
  };

  const selectedReferenceEntityAttribute =
    'attribute' === selection.type
      ? referenceEntityAttributes.find(attribute => selection.attribute_identifier === attribute.identifier) ?? null
      : null;

  return (
    <Collapse
      collapseButtonLabel={isSelectorCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
      label={
        <>
          {translate('akeneo.tailored_export.column_details.sources.selection.title')}
          {0 === validationErrors.length && !isDefaultReferenceEntitySelection(selection) && <Pill level="primary" />}
          {0 < validationErrors.length && <Pill level="danger" />}
        </>
      }
      isOpen={isSelectorCollapsed}
      onCollapse={toggleSelectorCollapse}
    >
      <Section>
        <AttributeSelectorContainer>
          <Field label={translate('pim_common.type')}>
            <SelectInput
              clearable={false}
              invalid={0 < typeErrors.length}
              emptyResultLabel={translate('pim_common.no_result')}
              openLabel={translate('pim_common.open')}
              value={'attribute' === selection.type ? selection.attribute_identifier : selection.type}
              onChange={handleTypeChange}
            >
              <SelectInput.Option title={translate('pim_common.code')} value="code">
                {translate('pim_common.code')}
              </SelectInput.Option>
              {referenceEntityAttributes.map(referenceEntityAttribute => (
                <SelectInput.Option
                  key={referenceEntityAttribute.identifier}
                  title={getLabel(referenceEntityAttribute.labels, catalogLocale, referenceEntityAttribute.code)}
                  value={referenceEntityAttribute.identifier}
                >
                  {getLabel(referenceEntityAttribute.labels, catalogLocale, referenceEntityAttribute.code)}
                </SelectInput.Option>
              ))}
            </SelectInput>
            {typeErrors.map((error, index) => (
              <Helper key={index} inline={true} level="error">
                {translate(error.messageTemplate, error.parameters)}
              </Helper>
            ))}
          </Field>
          {'attribute' === selection.type && null !== selectedReferenceEntityAttribute && (
            <AttributeSelector
              attribute={selectedReferenceEntityAttribute}
              selection={selection}
              channels={channels}
              validationErrors={validationErrors}
              onSelectionChange={onSelectionChange}
            />
          )}
        </AttributeSelectorContainer>
      </Section>
    </Collapse>
  );
};

export {ReferenceEntitySelector};
