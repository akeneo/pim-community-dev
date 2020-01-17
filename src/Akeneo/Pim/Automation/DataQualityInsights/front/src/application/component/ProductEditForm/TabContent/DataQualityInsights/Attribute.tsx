import React, {FunctionComponent} from 'react';

interface AttributeProps {
  code: string;
}

const Attribute: FunctionComponent<AttributeProps> = ({children}) => {
  return (
    <>
        <span className="AknDataQualityInsightsAttribute">{children}</span>
    </>
  );
};

export default Attribute;



