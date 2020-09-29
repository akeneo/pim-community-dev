import React, {FC, Fragment, ReactElement, ReactNode} from 'react';
import styled, {css} from 'styled-components';
import {LoadingPlaceholderContainer} from "./LoadingPlaceholder";

type ButtonCollectionProps = {
  userActionVisible: boolean;
};

const PageHeaderPlaceholder = styled.div`
  width: 200px;
  height: 34px;
`;

const Header = styled.header`
  position: sticky;
  top: 0;
  padding: 40px 40px 20px;
  background: white;
  z-index: 10;
  height: 130px;
`;

const ButtonCollection = styled.div<ButtonCollectionProps>`
  display: flex;
  align-items: center;
  margin-right: -10px;
  
  > :not(:first-child) {
    margin-left: 10px;
  }
  
  ${(props) => props.userActionVisible && css`
    border-left: 1px solid ${({theme}) => theme.color.grey80};
    margin-left: 20px;
    padding-left: 20px;
  `}
`;

const LineContainer = styled.div`
    display: flex;
    justify-content: space-between;
`;

const MainContainer = styled.div`
    flex-grow: 1;
    display: flex;
    justify-content: space-between;
    flex-direction: column;
    max-width: 100%;
`;

const State = styled.div`
  :not(:empty) {
    order: 50;
    align-self: center;
    margin-right: 0;
    margin-top: 6px;
    text-align: right;
    margin-left: 20px;
  }
`;

const Title = styled.div`
    color: ${({theme}) => theme.color.purple100};
    font-size: ${({theme}) => theme.fontSize.title};
    line-height: 34px;
    margin: 0;
    font-weight: normal;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    flex-grow: 1;

    &:first-letter {
      text-transform: uppercase;
    }
  }
`;

const Breadcrumbs = styled.div`
    flex-grow: 1;
    max-height: 32px;
    overflow: hidden;
    margin-right: 20px;
    line-height: 16px;
    white-space: nowrap;
    text-overflow: ellipsis;
`;

const ButtonsContainer = styled.div`
    display: flex;
    align-content: baseline;
`;

const ImageContainer = styled.div`
  position: relative;
  width: 142px;
  height: 142px;
  border: 1px solid ${({theme}) => theme.color.grey80};
  margin-right: 20px;
  border-radius: 4px;
  display: flex;
  overflow: hidden;
  flex-basis: 142px;
  flex-shrink: 0;
  
  img {
    max-height: 140px;
    max-width: 140px;
    width: auto;
  }
`;


type PageHeaderProps = {
  breadcrumb?: ReactElement;
  buttons?: ReactElement[];
  userButtons?: ReactNode;
  state?: ReactNode;
  imageSrc?: string;
  showPlaceholder?: boolean;
};

const PageHeader: FC<PageHeaderProps> = ({
  children: title,
  breadcrumb,
  buttons,
  userButtons,
  state,
  imageSrc,
  showPlaceholder
}) => (
  <Header>
    <LineContainer>
      {imageSrc && (
        <ImageContainer>
          <img src={imageSrc} alt={title as string} />
        </ImageContainer>
      )}

      <MainContainer>
        <div>
          <LineContainer>
            <Breadcrumbs>{breadcrumb}</Breadcrumbs>
            <ButtonsContainer>
              {userButtons}
              {buttons && (
                <ButtonCollection userActionVisible={userButtons !== undefined}>
                  {buttons.map((button, index) => (
                    <Fragment key={index}>{button}</Fragment>
                  ))}
                </ButtonCollection>
              )}
            </ButtonsContainer>
          </LineContainer>
          <LineContainer>
            <Title>
              {showPlaceholder ? (
                  <LoadingPlaceholderContainer>
                    <PageHeaderPlaceholder />
                  </LoadingPlaceholderContainer>
              ) : (
                  <>{title}</>
              )}
            </Title>
            <State>{state}</State>
          </LineContainer>
        </div>
      </MainContainer>
    </LineContainer>
  </Header>
);

export {PageHeader, PageHeaderPlaceholder};
