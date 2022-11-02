import React, {useCallback, forwardRef, useMemo, memo} from 'react';
import styled from 'styled-components';
import {
  AttributeBooleanIcon,
  AttributeLinkIcon,
  AttributeNumberIcon,
  AttributePriceIcon,
  AttributeTextIcon,
  getColor,
  GroupsIcon,
  Helper,
  MetricIcon,
  Pill,
  Table,
  TagIcon,
  useTheme,
} from 'akeneo-design-system';
import {getLabel, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {DataMapping} from '../../models/DataMapping';
import {useValidationErrors} from '../../contexts';
import {useAssociationTypes, useAttributes} from '../../hooks';
import {useRequirement} from '../../contexts/RequirementsContext';
import {getRequirementLabel, RequirementType} from '../../models';

const SourceList = styled.div`
  margin-left: 20px;
  text-overflow: ellipsis;
  overflow: hidden;
`;

const SourceListPlaceholder = styled.span`
  color: ${getColor('grey', 100)};
  font-style: italic;
  display: flex;
  align-items: center;
  width: 100%;
`;

const CellPlaceholder = styled(Table.Cell)`
  width: 40px;
`;

const IconCell = styled(Table.Cell)`
  width: 18px;
  padding-right: 0;
`;

const RequiredCell = styled(Table.Cell)`
  width: 10px;
`;

const TargetCell = styled(Table.Cell)`
  width: 200px;
  max-width: unset;
`;

const Spacer = styled.div`
  flex: 1;
`;

const TypeIcon = ({type}: {type: RequirementType}) => {
  const theme = useTheme();
  const props = {
    size: 18,
    color: getColor('grey', 100)({theme}),
  };

  switch (type) {
    case 'string':
      return <AttributeTextIcon {...props} />;
    case 'number':
    case 'integer':
      return <AttributeNumberIcon {...props} />;
    case 'boolean':
      return <AttributeBooleanIcon {...props} />;
    case 'url':
      return <AttributeLinkIcon {...props} />;
    case 'string_collection':
      return <GroupsIcon {...props} />;
    case 'measurement':
      return <MetricIcon {...props} />;
    case 'limited_string':
      return <TagIcon {...props} />;
    case 'price':
      return <AttributePriceIcon {...props} />;
    default:
      return <AttributeTextIcon {...props} />;
  }
};

type DataMappingRowProps = {
  dataMapping: DataMapping;
  isSelected: boolean;
  onDataMappingSelected: (uuid: string | null) => void;
};

const DataMappingRow = memo(
  forwardRef<HTMLInputElement, DataMappingRowProps>(
    ({dataMapping, isSelected, onDataMappingSelected, ...rest}: DataMappingRowProps, ref) => {
      const translate = useTranslate();

      const targetErrors = useValidationErrors(`[data_mappings][${dataMapping.uuid}][target]`, true);
      const hasError =
        useValidationErrors(`[data_mappings][${dataMapping.uuid}]`).length > 0 && 0 === targetErrors.length;
      const userContext = useUserContext();
      const catalogLocale = userContext.get('catalogLocale');
      const attributeCodes = useMemo(
        () => dataMapping.sources.filter(({type}) => 'attribute' === type).map(({code}) => code),
        [dataMapping.sources]
      );

      const associationTypeCodes = useMemo(
        () => dataMapping.sources.filter(({type}) => 'association_type' === type).map(({code}) => code),
        [dataMapping.sources]
      );

      const [, attributes] = useAttributes(attributeCodes);
      const [, associationTypes] = useAssociationTypes(associationTypeCodes);
      const requirement = useRequirement(dataMapping.target.name);

      const handleDataMappingSelected = useCallback(() => {
        onDataMappingSelected(dataMapping.uuid);
      }, [onDataMappingSelected, dataMapping.uuid]);

      if (null === requirement) {
        return null;
      }

      return (
        <>
          <Table.Row onClick={handleDataMappingSelected} isSelected={isSelected} {...rest}>
            <IconCell title={requirement.type}>
              <TypeIcon type={requirement.type} />
            </IconCell>
            <TargetCell title={requirement.code}>
              {getRequirementLabel(requirement)}
              {targetErrors.map((error, index) => (
                <Helper key={index} inline={true} level="error">
                  {translate(error.messageTemplate, error.parameters)}
                </Helper>
              ))}
            </TargetCell>
            <RequiredCell>
              {requirement.required && <Pill level={dataMapping.sources.length !== 0 ? 'primary' : 'warning'} />}
            </RequiredCell>
            <Table.Cell>
              <SourceList>
                {0 === dataMapping.sources.length ? (
                  <SourceListPlaceholder>
                    {translate('akeneo.syndication.data_mapping_list.data_mapping_row.no_source')}
                  </SourceListPlaceholder>
                ) : (
                  dataMapping.sources
                    .map(source =>
                      'attribute' === source.type
                        ? getLabel(
                            attributes.find(attribute => attribute.code === source.code)?.labels ?? {},
                            catalogLocale,
                            source.code
                          )
                        : 'association_type' === source.type
                        ? getLabel(
                            associationTypes.find(associationType => associationType.code === source.code)?.labels ??
                              {},
                            catalogLocale,
                            source.code
                          )
                        : 'static' === source.type
                        ? translate(`akeneo.syndication.data_mapping_details.sources.static.${source.code}.title`)
                        : translate(`pim_common.${source.code}`)
                    )
                    .join(', ')
                )}
              </SourceList>
              <Spacer />
              {hasError && <Pill level="danger" />}
            </Table.Cell>
            <CellPlaceholder />
          </Table.Row>
        </>
      );
    }
  )
);

export {DataMappingRow};
