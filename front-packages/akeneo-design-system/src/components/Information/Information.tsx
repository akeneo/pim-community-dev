import React, {isValidElement, ReactNode, Ref} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor, getFontSize} from '../../theme';
import {IllustrationProps} from '../../illustrations/IllustrationProps';

const Container = styled.div`
  align-items: center;
  display: flex;
  font-weight: 400;
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

const HelperTitle = styled.div`
  color: ${getColor('grey140')};
  font-size: ${getFontSize('bigger')};
  font-weight: 700;
`;

const ContentContainer = styled.div`
  padding: 10px 0px 10px 0px;
`;

type InformationProps = {
  /**
   * Define the illustration showed at left of the component.
   */
  illustration: ReactNode;

  /**
   * The title of the component.
   */
  title: ReactNode;

  /**
   * The content of the component.
   */
  children: ReactNode;
};

/** Information informs the user about the page's features */
const Information = React.forwardRef<HTMLDivElement, InformationProps>(
  ({illustration, title, children, ...rest}: InformationProps, forwardedRef: Ref<HTMLDivElement>) => {
    const resizedIllustration =
      isValidElement<IllustrationProps>(illustration) && React.cloneElement(illustration, {size: 80});

    return (
      <Container ref={forwardedRef} {...rest}>
        <IconContainer>{resizedIllustration}</IconContainer>
        <ContentContainer>
          <HelperTitle>{title}</HelperTitle>
          {children}
        </ContentContainer>
      </Container>
    );
  }
);

const HighlightTitle = styled.span<AkeneoThemedProps>`
  color: ${getColor('brand', 100)};
`;

export {Information, HighlightTitle};
