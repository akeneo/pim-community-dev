import React, {useMemo, useState} from 'react';
import {AttributesIllustration, Helper, Link, SectionTitle, uuid} from 'akeneo-design-system';
import {NoDataSection, NoDataText, NoDataTitle, useTranslate} from '@akeneo-pim-community/shared';
import {AddPropertyButton, DelimiterEdit, Preview, PropertiesList, PropertyEdit} from './structure';
import {Delimiter, Property, Structure as StructureType} from '../models';
import {Styled} from '../components/Styled';
import {TranslationWithLink} from '../components';

type StructureTabProps = {
  initialStructure: StructureType;
  delimiter: Delimiter | null;
  onStructureChange: (structure: StructureType) => void;
  onDelimiterChange: (delimiter: Delimiter | null) => void;
};

type PropertyId = string;
type StructureWithIdentifiers = (Property & {id: PropertyId})[];
const LIMIT_NUMBER = 20;

const StructureTab: React.FC<StructureTabProps> = ({
  initialStructure,
  delimiter,
  onStructureChange,
  onDelimiterChange,
}) => {
  const translate = useTranslate();
  const [selectedPropertyId, setSelectedPropertyId] = useState<PropertyId | undefined>();
  const structure = useMemo(
    () =>
      initialStructure.map(property => ({
        id: uuid(),
        ...property,
      })),
    [initialStructure]
  );
  const selectedProperty = useMemo(
    () => structure.find(propertyWithId => propertyWithId.id === selectedPropertyId),
    [selectedPropertyId, structure]
  );
  const isLimitReached = useMemo(() => structure.length === LIMIT_NUMBER, [structure.length]);

  const onPropertyChange = (property: Property) => {
    if (selectedProperty) {
      const updatedPropertyIndex = structure.findIndex(p => selectedProperty.id === p.id);
      const clonedStructure = [...structure];
      clonedStructure[updatedPropertyIndex] = {...property, id: selectedProperty.id};
      onStructureChange(clonedStructure);
    }
  };

  const onAddProperty = (property: Property) => {
    const newPropertyId = uuid();
    onStructureChange([...structure, {...property, id: newPropertyId}]);
    setSelectedPropertyId(newPropertyId);
  };

  const onDeleteProperty = (propertyId: PropertyId) => {
    const newStructure = structure.filter(property => property.id !== propertyId);
    onStructureChange(newStructure);
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
              <PropertiesList
                structure={structure}
                onSelect={setSelectedPropertyId}
                selectedId={selectedPropertyId}
                onChange={onStructureChange}
                onDelete={onDeleteProperty}
              />
              <DelimiterEdit
                delimiter={delimiter}
                onToggleDelimiter={onToggleDelimiter}
                onChangeDelimiter={onDelimiterChange}
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
