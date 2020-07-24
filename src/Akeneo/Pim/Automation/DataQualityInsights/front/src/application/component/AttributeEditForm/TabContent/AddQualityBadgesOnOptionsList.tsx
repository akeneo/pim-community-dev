import React, {FC, useCallback, useEffect, useLayoutEffect} from 'react';
import {useAttributeSpellcheckEvaluationContext} from "../../../context/AttributeSpellcheckEvaluationContext";
import {useAttributeOptionsListContext} from "../../../context/AttributeOptionsListContext";
import QualityBadge from "../../Common/QualityBadge";
import {useVisibleAttributeOptions} from "../../../../infrastructure/hooks/AttributeEditForm/useVisibleAttributeOptions";
import {SpellcheckEvaluation} from "../../../../infrastructure/hooks/AttributeEditForm/useSpellcheckEvaluationState";

const goodBadge = <QualityBadge label={'good'} />;
const toImproveBadge = <QualityBadge label={'to_improve'} />;
const naBadge = <QualityBadge label={'n_a'} />;

const AttributeOptionQualityBadge: FC<{option: string, evaluation: SpellcheckEvaluation}> = ({option, evaluation}) => {
  if (!evaluation.options[option]) {
    return naBadge;
  }

  const isToImprove = (evaluation.options[option].toImprove > 0) || false;
  return isToImprove ? toImproveBadge: goodBadge;
}

const AddQualityBadgesOnOptionsList: FC = () => {
  const {attributeOptions, addExtraData, removeExtraData} = useAttributeOptionsListContext();
  const {evaluation} = useAttributeSpellcheckEvaluationContext();
  const {visibleOptions} = useVisibleAttributeOptions();
  const displayedBadges: {[option: string]: boolean} = {};

  const showQualityBadge = useCallback((option: string, evaluation: SpellcheckEvaluation) => {
    if (displayedBadges[option]) {
      return;
    }
    addExtraData(option, <AttributeOptionQualityBadge option={option} evaluation={evaluation} />);
    displayedBadges[option] = true;
  }, [addExtraData]);

  const hideQualityBadge = useCallback((option: string) => {
    removeExtraData(option);
  }, [removeExtraData]);

  useLayoutEffect(() => {
    let ticking = false;
    let requestAnimationFrameId: number|null = null;
    if (!ticking) {
      requestAnimationFrameId = window.requestAnimationFrame(() => {
        visibleOptions.forEach((option) => {
          showQualityBadge(option, evaluation);
        });
        ticking = true;
      });
    }

    return () => {
      if (requestAnimationFrameId) {
        window.cancelAnimationFrame(requestAnimationFrameId);
      }
    }
  }, [visibleOptions, evaluation]);

  useEffect(() => {
    return () => {
      if (attributeOptions !== null) {
        attributeOptions.forEach((attributeOption) => {
          hideQualityBadge(attributeOption.code);
        });
      }
    }
  }, []);

  return <></>;
}

export default AddQualityBadgesOnOptionsList;
