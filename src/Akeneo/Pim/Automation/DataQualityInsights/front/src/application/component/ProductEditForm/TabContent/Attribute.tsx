import React, {FunctionComponent} from 'react';
import styled from "styled-components";
import {DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE} from "../../../../infrastructure/context-provider";

interface AttributeProps {
  isClickable: boolean;
  code: string;
}

const handleClick = (attributeCode: string) => {
  window.dispatchEvent(new CustomEvent(DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE, {detail: {
    code: attributeCode,
  }}));
};

const Content = styled.span`
  display: inline-block;
  font-style: italic;
  text-transform: capitalize;
`;

const Button = styled.button`
  vertical-align: initial;
`;

const Attribute: FunctionComponent<AttributeProps> = ({children, isClickable, code}) => {
  return (
    <>
      {isClickable ? (
        <Button onClick={() => handleClick(code)} className="AknActionButton AknActionButton--withoutBorder">
          <Content className="AknActionButton-highlight">{children}</Content>
        </Button>
      ) : (
        <Content className="AknActionButton-highlight">{children}</Content>
      )}
    </>
  );
};

export default Attribute;



