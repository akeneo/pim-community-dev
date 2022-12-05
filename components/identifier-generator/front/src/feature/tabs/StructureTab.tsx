import React, {useMemo, useState} from 'react';
import {AttributesIllustration, Helper, Link, Placeholder, SectionTitle, uuid} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {AddPropertyButton, DelimiterEdit, Preview, PropertiesList, PropertyEdit} from './structure';
import {Delimiter, Property, Structure} from '../models';
import {Styled} from '../components/Styled';
import {TranslationWithLink} from '../components';
import styled from 'styled-components';
import {Violation} from '../validators';

type StructureTabProps = {
  initialStructure: Structure;
  delimiter: Delimiter | null;
  onStructureChange: (structure: Structure) => void;
  onDelimiterChange: (delimiter: Delimiter | null) => void;
  validationErrors: Violation[];
};

type PropertyId = string;
type StructureWithIdentifiers = (Property & {id: PropertyId})[];
const LIMIT_NUMBER = 20;

const StructureDataContainer = styled.div`
  overflow-y: auto;
  max-height: calc(100vh - 450px);
`;

const StructureTab: React.FC<StructureTabProps> = ({
  initialStructure,
  delimiter,
  onStructureChange,
  onDelimiterChange,
  validationErrors,
}) => {
  const translate = useTranslate();
  const [selectedPropertyId, setSelectedPropertyId] = useState<PropertyId | undefined>();
  const [structure, setStructure] = useState<StructureWithIdentifiers>(
    initialStructure.map(property => ({
      id: uuid(),
      ...property,
    }))
  );
  const selectedProperty = useMemo(
    () => structure.find(propertyWithId => propertyWithId.id === selectedPropertyId),
    [selectedPropertyId, structure]
  );
  const isLimitReached = useMemo(() => structure.length === LIMIT_NUMBER, [structure.length]);

  const removeIdentifiers: (structureWithIdentifiers: StructureWithIdentifiers) => Structure =
    structureWithIdentifiers => {
      return structureWithIdentifiers.map(propertyWithIdentifier => {
        // eslint-disable-next-line @typescript-eslint/no-unused-vars
        const {id, ...property} = propertyWithIdentifier;

        return property;
      });
    };

  const displayedErrors = useMemo(
    () => validationErrors?.map(({message}) => message).filter((value, index, self) => self.indexOf(value) === index),
    [validationErrors]
  );

  const onPropertyChange = (property: Property) => {
    if (selectedProperty) {
      const updatedPropertyIndex = structure.findIndex(p => selectedProperty.id === p.id);
      const newStructure = [...structure];
      newStructure[updatedPropertyIndex] = {...property, id: selectedProperty.id};
      setStructure(newStructure);
      onStructureChange(removeIdentifiers(newStructure));
    }
  };

  const onAddProperty = (property: Property) => {
    const newPropertyId = uuid();
    const newStructure = [...structure, {...property, id: newPropertyId}];
    setStructure(newStructure);
    onStructureChange(removeIdentifiers(newStructure));
    setSelectedPropertyId(newPropertyId);
  };

  const onDeleteProperty = (propertyId: PropertyId) => {
    const newStructure = structure.filter(property => property.id !== propertyId);
    setStructure(newStructure);
    onStructureChange(removeIdentifiers(newStructure));
  };

  const onReorder = (indices: number[]) => {
    const newStructure = indices.map(i => structure[i]);
    setStructure(newStructure);
    onStructureChange(removeIdentifiers(newStructure));
  };

  const onToggleDelimiter = () => {
    delimiter === null ? onDelimiterChange('-') : onDelimiterChange(null);
  };

  return (
    <>
      <Helper>
        <TranslationWithLink
          translationKey={'pim_identifier_generator.structure.helper'}
          href={'https://help.akeneo.com/pim/serenity/articles/generate-product-identifiers.html'}
          linkKey={'pim_identifier_generator.structure.helper_link'}
        />
      </Helper>
      {displayedErrors?.length > 0 && (
        <Styled.MainErrorHelper level="error">
          <Styled.ErrorList>
            {displayedErrors.map(message => (
              <li key={message}>{message}</li>
            ))}
          </Styled.ErrorList>
        </Styled.MainErrorHelper>
      )}
      <Styled.TwoColumns withoutSecondColumn={!selectedProperty}>
        <div>
          <SectionTitle>
            <SectionTitle.Title>{translate('pim_identifier_generator.structure.title')}</SectionTitle.Title>
            <SectionTitle.Spacer />
            {!isLimitReached && <AddPropertyButton onAddProperty={onAddProperty} />}
          </SectionTitle>
          {structure.length > 0 && (
            <>
              {isLimitReached && <Helper>{translate('pim_identifier_generator.structure.limit_reached')}</Helper>}
              <Preview structure={structure} delimiter={delimiter} />
              <StructureDataContainer>
                <PropertiesList
                  structure={structure}
                  onSelect={setSelectedPropertyId}
                  selectedId={selectedPropertyId}
                  onReorder={onReorder}
                  onDelete={onDeleteProperty}
                  validationErrors={validationErrors}
                />
                <DelimiterEdit
                  delimiter={delimiter}
                  onToggleDelimiter={onToggleDelimiter}
                  onChangeDelimiter={onDelimiterChange}
                />
              </StructureDataContainer>
            </>
          )}
          {structure.length === 0 && (
            <Placeholder
              illustration={<AttributesIllustration />}
              size="large"
              title={translate('pim_identifier_generator.structure.empty.title')}
            >
              {translate('pim_identifier_generator.structure.empty.text')}
              <Link>{translate('pim_identifier_generator.structure.empty.link_text')}</Link>
            </Placeholder>
          )}
        </div>
        {selectedProperty && <PropertyEdit selectedProperty={selectedProperty} onChange={onPropertyChange} />}
      </Styled.TwoColumns>
    </>
  );
};

export {StructureTab};
export type {StructureWithIdentifiers, PropertyId};
