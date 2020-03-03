import React, {FunctionComponent, ReactElement} from 'react';
import {DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE} from "../../../../listener";

interface AttributeProps {
  code: string;
  label: string;
  separator: ReactElement | null;
  isLinkAvailable: boolean;
}

const handleClick = (attributeCode: string) => {
  window.dispatchEvent(new CustomEvent(DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE, {
    detail: {
      code: attributeCode,
    }
  }));
};

const Attribute: FunctionComponent<AttributeProps> = ({code, label, separator, isLinkAvailable}) => {

  const content =
    <>
      <span data-testid={"dqiAttributeWithRecommendation"}>{label}</span>
      {separator}
    </>;

  return !isLinkAvailable ? (
      <span className="AknDataQualityInsightsAttribute">{content}</span>
  ) : (
      <button onClick={() => handleClick(code)} className="AknActionButton AknActionButton--withoutBorder AknDataQualityInsightsAttribute AknDataQualityInsightsAttribute--link">
        {content}
      </button>
  );
};

export default Attribute;



