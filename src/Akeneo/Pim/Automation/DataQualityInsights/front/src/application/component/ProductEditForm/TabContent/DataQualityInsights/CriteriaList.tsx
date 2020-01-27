import React, {FunctionComponent} from 'react';
import Criterion from "./Criterion";
import {Rate, Recommendation} from "../../../../../domain";


interface CriteriaListProps {
  recommendations: Recommendation[],
  rates: Rate[],
  axis: string;
}

const getCriterionRate = (criterion: string, rates: Rate[]) => {
  return rates.find((item) => item.criterion === criterion);
};

const CriteriaList: FunctionComponent<CriteriaListProps> = ({axis, recommendations, rates}) => {
  return (
    <div>
      <ul>
            {recommendations.map((recommendation, index) => (
              <Criterion key={`${axis}-${index}`} recommendation={recommendation} rate={getCriterionRate(recommendation.criterion as string, rates)}/>
              ))}
      </ul>
    </div>
  );
};

export default CriteriaList;
