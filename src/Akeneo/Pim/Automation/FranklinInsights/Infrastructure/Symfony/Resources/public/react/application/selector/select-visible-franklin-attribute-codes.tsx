import {AttributeMappingStatus} from '../../domain/model/attribute-mapping-status.enum';
import {AttributesMapping} from '../../domain/model/attributes-mapping';
import {FamilyMappingState} from '../reducer/family-mapping';

export function selectFilteredFranklinAttributeCodes(state: FamilyMappingState): string[] {
  const searchTerms: string = state.searchFranklinAttributes.codeOrLabel
    ? escape(state.searchFranklinAttributes.codeOrLabel.toLowerCase())
    : '';
  const status: AttributeMappingStatus | null = state.searchFranklinAttributes.status;
  const mapping: AttributesMapping = state.familyMapping.mapping;

  const filteredMapping =
    '' === searchTerms && null === status
      ? Object.entries(mapping)
      : Object.entries(mapping).filter(([franklinAttributeCode, mapping]) => {
          const hasMatchingStatus: boolean = status ? mapping.status === status : true;

          return (
            (RegExp(searchTerms).test(franklinAttributeCode.toLowerCase()) ||
              RegExp(searchTerms).test(mapping.franklinAttribute.label.toLowerCase())) &&
            hasMatchingStatus
          );
        });

  return filteredMapping.reduce((rows: string[], [franklinAttributeCode]): string[] => {
    rows.push(franklinAttributeCode);

    return rows;
  }, []);
}

function escape(value: string): string {
  return value.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
}
