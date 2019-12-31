import React, {FunctionComponent} from 'react';
import styled from "styled-components";

interface AttributeProps {
  isClickable: boolean;
}

const handleClick = (attribute: string) => {
  alert(`attribute: [${attribute}]`)
};

const Content = styled.span`
  display: inline-block;
  font-style: italic;
  text-transform: capitalize;
`;

const Button = styled.button`
  vertical-align: initial;
`;

const Attribute: FunctionComponent<AttributeProps> = ({children, isClickable}) => {
  return (
    <>
      {isClickable ? (
        <Button onClick={() => handleClick(children as string)} className="AknActionButton AknActionButton--withoutBorder">
          <Content className="AknActionButton-highlight">{children}</Content>
        </Button>
      ) : (
        <Content className="AknActionButton-highlight">{children}</Content>
      )}
    </>
  );
};

export default Attribute;



