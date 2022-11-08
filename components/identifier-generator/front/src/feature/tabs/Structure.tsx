import React, {useState} from 'react';
import {AttributesIllustration, Link, SectionTitle, uuid} from 'akeneo-design-system';
import {NoDataSection, NoDataText, NoDataTitle, useTranslate} from '@akeneo-pim-community/shared';
import {AddPropertyButton} from './structure/AddPropertyButton';
import {Delimiter, Property, PropertyWithIdentifier, Structure as StructureType} from '../models';
import {PropertiesList} from './structure/PropertiesList';
import {Preview} from './structure/Preview';
import {PropertyEdit} from './structure/PropertyEdit';
import styled from 'styled-components';

const StructureSectionTitle = styled(SectionTitle)`
  justify-content: space-between;
  margin-top: 20px;
  padding-bottom: 10px;
`;
const StructureContainer = styled.div`
  display: flex;
  flex-direction: row;
`;
const PropertiesSection = styled.div`
  width: 100%;
  height: 100vh;
`;
const FormSection = styled.div`
  max-width: 30%;
  height: 100vh;
  margin-left: 40px;
`;

type StructureTabProps = {
  initialStructure: StructureType;
  delimiter: Delimiter | null;
  onStructureChange: (structure: StructureType) => void;
};

type StructureWithIdentifiers = PropertyWithIdentifier[];

const Structure: React.FC<StructureTabProps> = ({initialStructure, delimiter, onStructureChange}) => {
  const translate = useTranslate();
  const [selectedPropertyId, setSelectedPropertyId] = useState<string | null>(null);
  const [structure, setStructure] = useState<StructureWithIdentifiers>(
    initialStructure.map(property => {
      return {
        id: uuid(),
        ...property,
      };
    })
  );

  const onSelectedPropertyChange = (id: string) => {
    setSelectedPropertyId(id);
  };

  const onPropertyChange = (propertyWithId: PropertyWithIdentifier) => {
    const updatedPropertyIndex = structure.findIndex(p => propertyWithId.id === p.id);
    structure[updatedPropertyIndex] = propertyWithId;
    setStructure(structure);
    onStructureChange(structure);
  };

  const onAddProperty = (property: Property) => {
    const newPropertyUuid = uuid();
    structure.push({...property, id: newPropertyUuid});
    setStructure(structure);
    onStructureChange(structure);
    setSelectedPropertyId(newPropertyUuid);
  };

  const selectedProperty = structure.find(propertyWithId => propertyWithId.id === selectedPropertyId);

  return (
    <>
      <StructureSectionTitle>
        <SectionTitle.Title>{translate('pim_identifier_generator.structure.title')}</SectionTitle.Title>
        <AddPropertyButton onAddProperty={onAddProperty} />
      </StructureSectionTitle>
      <StructureContainer>
        <PropertiesSection>
          {structure.length > 0 && (
            <>
              <Preview structure={structure} delimiter={delimiter}/>
              <PropertiesList structure={structure} onChange={onSelectedPropertyChange} />
            </>
          )}
          {structure.length === 0 && (
            <NoDataSection>
              <AttributesIllustration size={256} />
              <NoDataTitle>{translate('pim_identifier_generator.structure.empty.title')}</NoDataTitle>
              <NoDataText>
                <p>{translate('pim_identifier_generator.structure.empty.text')}</p>
                <Link>{translate('pim_identifier_generator.structure.empty.link_text')}</Link>
              </NoDataText>
            </NoDataSection>
          )}
        </PropertiesSection>
        <FormSection>
          {selectedProperty && <PropertyEdit selectedProperty={selectedProperty} onChange={onPropertyChange} />}
        </FormSection>
      </StructureContainer>
    </>
  );
};

export {Structure};
export type {StructureWithIdentifiers};
