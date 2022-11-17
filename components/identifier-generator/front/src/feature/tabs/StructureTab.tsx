import React, {useState} from 'react';
import {AttributesIllustration, Helper, Link, SectionTitle, uuid} from 'akeneo-design-system';
import {NoDataSection, NoDataText, NoDataTitle, useTranslate} from '@akeneo-pim-community/shared';
import {AddPropertyButton, Preview, PropertiesList, PropertyEdit} from './structure';
import {Delimiter, Property, Structure as StructureType} from '../models';
import {Styled} from '../components/Styled';
import {TranslationWithLink} from '../components';

type StructureTabProps = {
  initialStructure: StructureType;
  delimiter: Delimiter | null;
  onStructureChange: (structure: StructureType) => void;
};

type PropertyId = string;
type StructureWithIdentifiers = (Property & {id: PropertyId})[];

const StructureTab: React.FC<StructureTabProps> = ({initialStructure, delimiter, onStructureChange}) => {
  const translate = useTranslate();
  const [selectedPropertyId, setSelectedPropertyId] = useState<PropertyId | undefined>();
  const [structure, setStructure] = useState<StructureWithIdentifiers>(
    initialStructure.map(property => {
      return {
        id: uuid(),
        ...property,
      };
    })
  );

  const onPropertyChange = (property: Property) => {
    if (selectedProperty) {
      const updatedPropertyIndex = structure.findIndex(p => selectedProperty.id === p.id);
      structure[updatedPropertyIndex] = {...property, id: selectedProperty.id};
      setStructure(structure);
      onStructureChange(structure);
    }
  };

  const onAddProperty = (property: Property) => {
    const newPropertyId = uuid();
    structure.push({...property, id: newPropertyId});
    setStructure(structure);
    onStructureChange(structure);
    setSelectedPropertyId(newPropertyId);
  };

  const selectedProperty = structure.find(propertyWithId => propertyWithId.id === selectedPropertyId);

  return (
    <>
      <Helper>
        <TranslationWithLink
          translationKey={'pim_identifier_generator.structure.helper'}
          href={'https://help.akeneo.com/pim/serenity/articles/generate-product-identifiers.html'}
          linkKey={'pim_identifier_generator.structure.helper_link'}
        />
      </Helper>
      <Styled.TwoColumns withoutSecondColumn={!selectedProperty}>
        <div>
          <SectionTitle>
            <SectionTitle.Title>{translate('pim_identifier_generator.structure.title')}</SectionTitle.Title>
            <SectionTitle.Spacer />
            <AddPropertyButton onAddProperty={onAddProperty} />
          </SectionTitle>
          {structure.length > 0 && (
            <>
              <Preview structure={structure} delimiter={delimiter} />
              <PropertiesList
                structure={structure}
                onSelect={setSelectedPropertyId}
                selectedId={selectedPropertyId}
                onChange={setStructure}
              />
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
        </div>
        {selectedProperty && (
          <div>
            <PropertyEdit selectedProperty={selectedProperty} onChange={onPropertyChange} />
          </div>
        )}
      </Styled.TwoColumns>
    </>
  );
};

export {StructureTab};
export type {StructureWithIdentifiers, PropertyId};
