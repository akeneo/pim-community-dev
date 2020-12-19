import React, {isValidElement, ReactNode, Ref} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor, getFontSize} from '../../theme';
import {IllustrationProps} from '../../illustrations/IllustrationProps';
import {useSkeleton} from '../../hooks';
import {applySkeletonStyle, SkeletonProps} from '../Skeleton/Skeleton';

const Container = styled.div<SkeletonProps & AkeneoThemedProps>`
  align-items: center;
  display: flex;
  font-weight: 400;
  padding-right: 15px;
  color: ${getColor('grey120')};
  min-height: 100px;
  background-color: ${getColor('blue10')};

  ${applySkeletonStyle()}
`;

const IconContainer = styled.span<SkeletonProps & AkeneoThemedProps>`
  height: 80px;
  padding: 0px 20px 0px 20px;
  margin: 20px 15px 20px 0px;
  ${({skeleton}) =>
    !skeleton &&
    css`
      border-right: 1px solid ${getColor('grey80')};
    `};

  & > svg {
    opacity: 0;
  }
`;

const HelperTitle = styled.div<SkeletonProps & AkeneoThemedProps>`
  color: ${getColor('grey140')};
  font-size: ${getFontSize('bigger')};
  font-weight: 700;

  ${applySkeletonStyle()}
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

    const skeleton = useSkeleton();

    return (
      <Container ref={forwardedRef} skeleton={skeleton} {...rest}>
        <IconContainer skeleton={skeleton}>{resizedIllustration}</IconContainer>
        <ContentContainer>
          <HelperTitle skeleton={skeleton}>{title}</HelperTitle>
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
