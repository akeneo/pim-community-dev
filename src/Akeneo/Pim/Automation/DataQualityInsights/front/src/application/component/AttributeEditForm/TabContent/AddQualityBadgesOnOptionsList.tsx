import React, {FC, useCallback, useEffect, useLayoutEffect, useState} from 'react';
import {useAttributeSpellcheckEvaluationContext} from '../../../context/AttributeSpellcheckEvaluationContext';
import {useAttributeOptionsListContext} from '../../../context/AttributeOptionsListContext';
import {useVisibleAttributeOptions} from '../../../../infrastructure/hooks/AttributeEditForm/useVisibleAttributeOptions';
import AttributeOptionQualityBadge from './AttributeOptionQualityBadge';

type DisplayedBadges = {
  [option: string]: {
    element: JSX.Element;
    loaded: boolean;
  };
};

const AddQualityBadgesOnOptionsList: FC = () => {
  const {attributeOptions, addExtraData} = useAttributeOptionsListContext();
  const {evaluation} = useAttributeSpellcheckEvaluationContext();
  const {visibleOptions} = useVisibleAttributeOptions();
  const [badges, setBadges] = useState<DisplayedBadges>({});

  useEffect(() => {
    if (attributeOptions !== null) {
      const newBadges: DisplayedBadges = {};

      attributeOptions.forEach(attributeOption => {
        newBadges[attributeOption.code] = {
          element: <AttributeOptionQualityBadge option={attributeOption.code} evaluation={evaluation} />,
          loaded: false,
        };
      });

      setBadges(newBadges);
    } else {
      setBadges({});
    }
  }, [attributeOptions, evaluation]);

  const showQualityBadge = useCallback(
    (option: string, badges: DisplayedBadges) => {
      if (badges[option] && badges[option].loaded) {
        return;
      }

      if (badges[option]) {
        addExtraData(option, badges[option].element);
      }

      setBadges(state => {
        return {
          ...state,
          [option]: {
            ...state.option,
            loaded: true,
          },
        };
      });
    },
    [addExtraData, setBadges]
  );

  useLayoutEffect(() => {
    const requestAnimationFrameIds: number[] = [];

    if (Object.keys(badges).length > 0) {
      visibleOptions.forEach(option => {
        const requestAnimationFrameId = window.requestAnimationFrame(() => {
          showQualityBadge(option, badges);
        });
        requestAnimationFrameIds.push(requestAnimationFrameId);
      });
    }

    return () => {
      requestAnimationFrameIds.forEach(requestAnimationFrameId => {
        window.cancelAnimationFrame(requestAnimationFrameId);
      });
    };
  }, [visibleOptions, badges]);

  return <></>;
};

export default AddQualityBadgesOnOptionsList;
