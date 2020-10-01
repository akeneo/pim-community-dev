import React, {Children, isValidElement, ReactNode, Ref} from 'react';
import styled from 'styled-components';
import {getColor, getFontSize} from '../../theme';

const Container = styled.div`
  align-items: center;
  display: flex;
  font-weight: 600;
  padding-right: 15px;
  color: ${getColor('grey120')};
  min-height: 100px;
  background-color: ${getColor('blue10')};
`;

const IconContainer = styled.span`
  height: 80px;
  padding: 0px 20px 0px 20px;
  margin: 20px 15px 20px 0px;
  border-right: 1px solid ${getColor('grey80')};
`;

const ContentContainer = styled.div`
  padding: 10px 0px 10px 0px;
`;

type HelperProps = {
  /**
   * Define the illustration showed at left of the component.
   */
  illustration: ReactNode;

  /**
   * The content of the component.
   */
  children: ReactNode;
};

const InformationHelper = React.forwardRef<HTMLDivElement, HelperProps>(
  ({children, illustration, ...rest}: HelperProps, forwardedRef: Ref<HTMLDivElement>) => {
    const titleChildren = Children.toArray(children).filter(
      child => isValidElement(child) && child.type === HelperTitle
    );
    const descriptionChildren = Children.toArray(children).filter(
      child => !isValidElement(child) || child.type !== HelperTitle
    );

    const resizedIllustration = isValidElement(illustration) && React.cloneElement(illustration, {size: 80});

    return (
      <Container ref={forwardedRef} {...rest}>
        <IconContainer>{resizedIllustration}</IconContainer>
        <ContentContainer>
          {titleChildren}
          {descriptionChildren}
        </ContentContainer>
      </Container>
    );
  }
);

const HelperTitle = styled.div`
  color: ${getColor('grey140')};
  font-size: ${getFontSize('bigger')};
  font-weight: 600;
`;

export {InformationHelper, HelperTitle};
