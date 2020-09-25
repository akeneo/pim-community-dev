import React, {FC, Fragment, ReactElement, ReactNode} from 'react';
import styled from 'styled-components';
import {LoadingPlaceholderContainer} from "./LoadingPlaceholder";

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

const ButtonCollection = styled.div.attrs(() => ({className: 'AknTitleContainer-actionsContainer AknButtonList'}))`
  > :not(:first-child) {
    margin-left: 10px;
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
    <div className="AknTitleContainer-line">
      {imageSrc && (
        <div className="AknImage AknImage--readOnly">
          <img className="AknImage-display" src={imageSrc} alt={title as string} />
        </div>
      )}

      <div className="AknTitleContainer-mainContainer">
        <div>
          <div className="AknTitleContainer-line">
            <div className="AknTitleContainer-breadcrumbs">{breadcrumb}</div>
            <div className="AknTitleContainer-buttonsContainer">
              {userButtons}
              {buttons && (
                <ButtonCollection>
                  {buttons.map((button, index) => (
                    <Fragment key={index}>{button}</Fragment>
                  ))}
                </ButtonCollection>
              )}
            </div>
          </div>
          <div className="AknTitleContainer-line">
            <div className="AknTitleContainer-title">
              {showPlaceholder ? (
                  <LoadingPlaceholderContainer>
                    <PageHeaderPlaceholder />
                  </LoadingPlaceholderContainer>
              ) : (
                  <>{title}</>
              )}
            </div>
            <div className="AknTitleContainer-state">{state}</div>
          </div>
        </div>
      </div>
    </div>
  </Header>
);

export {PageHeader, PageHeaderPlaceholder};
