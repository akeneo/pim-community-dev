import React, {FunctionComponent, ReactElement} from 'react';
import {followAttributeRecommendation, FollowAttributeRecommendationHandler} from '../../../../../user-actions';
import {useProduct, useProductFamily} from '../../../../../../infrastructure/hooks';

interface AttributeProps {
  attributeCode: string;
  label: string;
  separator: ReactElement | null;
  followRecommendation?: FollowAttributeRecommendationHandler;
}

const Attribute: FunctionComponent<AttributeProps> = ({
  attributeCode,
  label,
  separator,
  followRecommendation = followAttributeRecommendation,
}) => {
  const product = useProduct();
  const family = useProductFamily();

  const content = (
    <>
      <span data-testid={'dqiAttributeWithRecommendation'}>{label}</span>
      {separator}
    </>
  );

  return (
    <button
      className="AknActionButton AknActionButton--withoutBorder AknDataQualityInsightsAttribute AknDataQualityInsightsAttribute--link"
      onClick={() => followRecommendation(attributeCode, product, family)}
    >
      {content}
    </button>
  );
};

export default Attribute;
export type {FollowAttributeRecommendationHandler};
