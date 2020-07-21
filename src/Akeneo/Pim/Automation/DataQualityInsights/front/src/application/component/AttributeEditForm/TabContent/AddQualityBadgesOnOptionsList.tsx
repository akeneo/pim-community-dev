import React, {FC, useCallback, useEffect} from 'react';
import {useAttributeSpellcheckEvaluationContext} from "../../../context/AttributeSpellcheckEvaluationContext";
import {useAttributeOptionsListContext} from "../../../context/AttributeOptionsListContext";
import QualityBadge from "../../Common/QualityBadge";

const AddQualityBadgesOnOptionsList: FC = () => {
  const {attributeOptions, addExtraData, removeExtraData} = useAttributeOptionsListContext();
  const {evaluation} = useAttributeSpellcheckEvaluationContext();

  const addQualityBadges = useCallback(() => {
    if (attributeOptions === null) {
      return;
    }

    attributeOptions.forEach((attributeOptions) => {
      if (!evaluation.options[attributeOptions.code]) {
        return;
      }

      const isToImprove = (evaluation.options[attributeOptions.code].toImprove > 0) || false;
      const label = isToImprove ? 'to_improve': 'good';

      addExtraData(attributeOptions.code, <QualityBadge label={label} />);
    });
  }, [attributeOptions, evaluation, addExtraData]);

  const removeQualityBadges = useCallback(() => {
    if (attributeOptions === null) {
      return;
    }

    attributeOptions.forEach((attributeOptions) => {
      removeExtraData(attributeOptions.code);
    });
  }, [attributeOptions, removeExtraData]);

  useEffect(() => {
    addQualityBadges();

    return () => {
      removeQualityBadges();
    }
  }, [addQualityBadges, removeQualityBadges]);

  return <></>;
}

export default AddQualityBadgesOnOptionsList;
