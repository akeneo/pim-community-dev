import {fetchAllAttributeGroupsDqiStatus, fetchAttributeGroupsByCode} from '../../fetcher';
import {useEffect, useState} from 'react';
import {useCatalogContext, useProductFamily, useProductEvaluation} from '../index';
import {Attribute, Family as FamilyInformation} from '../../../domain';
import {AttributeGroupCollection} from '@akeneo-pim-community/settings-ui/src/models';
import {useMountedRef} from '@akeneo-pim-community/settings-ui/src/hooks';

const useProductEvaluatedAttributeGroups = () => {
  const family = useProductFamily();
  const {locale, channel} = useCatalogContext();
  const {evaluation} = useProductEvaluation();
  const [attributeGroupsStatus, setAttributeGroupsStatus] = useState<null | object>(null);
  const [evaluatedGroups, setEvaluatedGroups] = useState<null | AttributeGroupCollection>(null);
  const [allGroupsEvaluated, setAllGroupsEvaluated] = useState<boolean>(false);
  const mountedRef = useMountedRef();

  const extractFamilyAttributeGroupCodes = (family: FamilyInformation) => {
    let familyAttributeGroups = family.attributes.map((attribute: Attribute) => attribute.group);
    return Array.from(new Set(familyAttributeGroups)); //To remove duplicates (no native JS method)
  };

  const filterDisabledAttributeGroups = (allGroupsStatus: object, familyAttributeGroups: string[]) => {
    return Object.entries(allGroupsStatus)
      .filter(([groupCode, status]) => familyAttributeGroups.includes(groupCode) && status === true)
      .map(([groupCode, _]) => groupCode);
  };

  useEffect(() => {
    if (channel && locale && evaluation) {
      (async () => {
        const response = await fetchAllAttributeGroupsDqiStatus();
        if (mountedRef.current) {
          setAttributeGroupsStatus(response);
        }
      })();
    }
  }, [evaluation, locale, channel]);

  useEffect(() => {
    if (attributeGroupsStatus === null || !family || !family.attributes) {
      return;
    }

    (async () => {
      const familyAttributeGroupCodes = extractFamilyAttributeGroupCodes(family);
      const productEvaluatedGroupsCodes = filterDisabledAttributeGroups(
        attributeGroupsStatus,
        familyAttributeGroupCodes
      );

      if (productEvaluatedGroupsCodes.length === 0) {
        setEvaluatedGroups({});
        return;
      }

      if (productEvaluatedGroupsCodes.length !== familyAttributeGroupCodes.length) {
        const attributeGroups = await fetchAttributeGroupsByCode(productEvaluatedGroupsCodes);
        if (mountedRef.current) {
          setEvaluatedGroups(attributeGroups);
        }
      } else {
        setAllGroupsEvaluated(true);
      }
    })();
  }, [attributeGroupsStatus, family]);

  return {
    evaluatedGroups,
    allGroupsEvaluated,
  };
};

export {useProductEvaluatedAttributeGroups};
