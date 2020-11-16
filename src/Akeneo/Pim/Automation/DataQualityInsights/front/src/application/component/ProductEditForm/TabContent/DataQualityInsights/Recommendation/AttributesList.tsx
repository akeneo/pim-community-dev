import React, {FC} from 'react';
import AttributeWithRecommendation from '../../../../../../domain/AttributeWithRecommendation.interface';
import Attribute, {FollowAttributeRecommendationHandler} from './Attribute';
import {Evaluation} from '../../../../../../domain';
import {FollowAttributesListRecommendationHandler} from '../../../../../user-actions';
import {Recommendation} from './Recommendation';
import {TooManyAttributesLink} from './TooManyAttributesLink';
import {getAxisAttributesWithRecommendations} from '../../../../../helper';

const MAX_ATTRIBUTES_DISPLAYED = 15;

interface AttributesListProps {
  criterionCode: string;
  attributes: AttributeWithRecommendation[];
  axis: string;
  evaluation: Evaluation;
  followAttributeRecommendation?: FollowAttributeRecommendationHandler;
  followAttributesListRecommendation?: FollowAttributesListRecommendationHandler;
}

const AttributesList: FC<AttributesListProps> = ({
  criterionCode,
  attributes,
  axis,
  evaluation,
  followAttributeRecommendation,
  followAttributesListRecommendation,
}) => {
  const criteria = evaluation.criteria || [];
  const allAttributes = getAxisAttributesWithRecommendations(criteria);

  if (attributes.length === 0) {
    return <Recommendation type={'success'} />;
  }

  if (attributes.length <= MAX_ATTRIBUTES_DISPLAYED) {
    return (
      <>
        {attributes.map((attribute: AttributeWithRecommendation, index: number) => {
          const separator = (
            <>
              {index < attributes.length - 1 && <>,&thinsp;</>}
              {index === attributes.length - 1 && '.'}
            </>
          );

          return (
            <Attribute
              key={`attribute-${criterionCode}-${index}`}
              attributeCode={attribute.code}
              label={attribute.label}
              separator={separator}
              followRecommendation={followAttributeRecommendation}
            />
          );
        })}
      </>
    );
  } else {
    return (
      <TooManyAttributesLink
        axis={axis}
        attributes={allAttributes}
        numOfAttributes={attributes.length}
        followRecommendation={followAttributesListRecommendation}
      />
    );
  }
};

export default AttributesList;
