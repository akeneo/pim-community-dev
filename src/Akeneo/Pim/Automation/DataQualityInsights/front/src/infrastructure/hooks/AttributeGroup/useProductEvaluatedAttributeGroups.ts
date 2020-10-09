import {fetchAllAttributeGroupsDqiStatus, fetchAttributeGroupsByCode} from '../../fetcher';
import {useEffect, useState} from "react";
import {useCatalogContext, useFetchProductFamilyInformation, useProductEvaluation} from "../index";
import {Attribute, Evaluation, Family as FamilyInformation, ProductEvaluation} from "../../../domain";
import {AttributeGroupCollection} from "@akeneo-pim-community/settings-ui/src/models";
import {useMountedRef} from "@akeneo-pim-community/settings-ui/src/hooks";
import {get as _get} from "lodash";

const useProductEvaluatedAttributeGroups = () => {
  const family = useFetchProductFamilyInformation();
  const {locale, channel} = useCatalogContext();
  const {evaluation} = useProductEvaluation();
  const [attributeGroupsStatus, setAttributeGroupsStatus] = useState<null | object>(null);
  const [evaluatedGroups, setEvaluatedGroups] = useState<null | AttributeGroupCollection>(null);
  const [allGroupsEvaluated, setAllGroupsEvaluated] = useState<boolean>(false);
  const mountedRef = useMountedRef();

  const isProductEvaluationPending = (evaluation: ProductEvaluation, channel: string, locale: string) => {
    if (Object.keys(evaluation).length === 0) {
      return true;
    }

    const axisInProgress: any = Object.keys(evaluation).filter((axisCode: string) => {
      const axisEvaluation: Evaluation = _get(evaluation, [axisCode, channel, locale]);

      return axisEvaluation.rate?.value === null;
    });

    return axisInProgress.length > 0;
  }

  const extractFamilyAttributeGroupCodes = (family: FamilyInformation) => {
    let familyAttributeGroups = family.attributes.map((attribute: Attribute) => attribute.group);
    return Array.from(new Set(familyAttributeGroups)); //To remove duplicates (no native JS method)
  }

  const filterDisabledAttributeGroups = (allGroupsStatus: object, familyAttributeGroups: string[]) => {
    return Object.entries(allGroupsStatus)
      .filter(([groupCode, status]) => familyAttributeGroups.includes(groupCode) && status === true)
      .map(([groupCode, _]) => groupCode);
  }

  useEffect(() => {
    if (channel && locale && evaluation && !isProductEvaluationPending(evaluation, channel, locale)) {
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
      const productEvaluatedGroupsCodes = filterDisabledAttributeGroups(attributeGroupsStatus, familyAttributeGroupCodes);

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
        setEvaluatedGroups({});
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
