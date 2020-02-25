import React, {FunctionComponent, ReactElement} from 'react';
import {DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE} from "../../../../listener";

interface AttributeProps {
  code: string;
  label: string;
  separator: ReactElement | null;
}

const handleClick = (attributeCode: string) => {
  window.dispatchEvent(new CustomEvent(DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE, {
    detail: {
      code: attributeCode,
    }
  }));
};

const Attribute: FunctionComponent<AttributeProps> = ({code, label, separator}) => {

  return (
      <button onClick={() => handleClick(code)} className="AknActionButton AknActionButton--withoutBorder AttributeLink">
        <span className="AknDataQualityInsightsAttribute" data-testid={"dqiAttributeWithRecommendation"}>{label}</span>
        {separator}
      </button>
  );
};

export default Attribute;



