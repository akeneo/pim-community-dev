import React, {Fragment, PropsWithChildren, ReactElement, ReactNode} from 'react';
import styled from 'styled-components';

const Header = styled.header`
  position: sticky;
  top: 0;
  padding: 40px 40px 20px;
  background: white;
  z-index: 10;
`;

type PageHeaderProps = {
  breadcrumb?: ReactElement;
  buttons?: ReactElement[];
  userButtons?: ReactNode;
  state?: ReactNode;
  imageSrc?: string;
};

const PageHeader = ({children: title, breadcrumb, buttons, userButtons, state, imageSrc}: PropsWithChildren<PageHeaderProps>) => (
  <Header>
    <div className="AknTitleContainer-line">
      {imageSrc && (
        <div className="AknImage AknImage--readOnly">
          <img className="AknImage-display" src={imageSrc} />
        </div>
      )}

      <div className="AknTitleContainer-mainContainer">
        <div>
          <div className="AknTitleContainer-line">
            <div className="AknTitleContainer-breadcrumbs">{breadcrumb}</div>
            <div className="AknTitleContainer-buttonsContainer">
              {userButtons}
              {buttons && (
                <div className="AknTitleContainer-actionsContainer AknButtonList">
                  {buttons.map((button, index) => (
                    <Fragment key={index}>{button}</Fragment>
                  ))}
                </div>
              )}
            </div>
          </div>
          <div className="AknTitleContainer-line">
            <div className="AknTitleContainer-title">{title}</div>
            <div className="AknTitleContainer-state">{state}</div>
          </div>
        </div>
      </div>
    </div>
  </Header>
);

export {PageHeader};
