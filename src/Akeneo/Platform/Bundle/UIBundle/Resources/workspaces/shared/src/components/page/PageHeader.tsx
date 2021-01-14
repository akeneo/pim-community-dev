import React, {Children, cloneElement, FC, isValidElement, ReactElement, ReactNode} from 'react';
import styled from 'styled-components';
import {Actions, Breadcrumb, Illustration, IllustrationProps, State, Title, UserActions, Content} from './header';

const Header = styled.header`
  position: sticky;
  top: 0;
  padding: 40px 40px 20px;
  background: white;
  z-index: 10;
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

const ActionsContainer = styled.div`
  display: flex;
  align-content: baseline;
  gap: 10px;
`;

type PageHeaderProps = {
  showPlaceholder?: boolean;
};

type HeaderElements = {
  illustration: ReactElement | undefined;
  breadcrumb: ReactElement | undefined;
  title: ReactElement | undefined;
  state: ReactElement | undefined;
  actions: ReactElement | undefined;
  userActions: ReactElement | undefined;
  content: ReactElement | undefined;
};

const buildHeaderElements = (children: ReactNode | undefined, showPlaceholder?: boolean): HeaderElements => {
  const headerElements: HeaderElements = {
    illustration: undefined,
    breadcrumb: undefined,
    title: undefined,
    state: undefined,
    actions: undefined,
    userActions: undefined,
    content: undefined,
  };

  Children.forEach(children, child => {
    if (!isValidElement(child)) {
      return;
    }

    switch (child.type) {
      case Illustration:
        headerElements.illustration = child;
        break;
      case Breadcrumb:
        headerElements.breadcrumb = child;
        break;
      case Title:
        headerElements.title = React.cloneElement(child, {
          showPlaceholder,
        });
        break;
      case State:
        headerElements.state = child;
        break;
      case Actions:
        headerElements.actions = child;
        break;
      case UserActions:
        headerElements.userActions = child;
        break;
      case Content:
        headerElements.content = child;
        break;
    }
  });

  if (headerElements.userActions !== undefined && headerElements.actions !== undefined) {
    headerElements.actions = cloneElement(headerElements.actions, {
      userActionVisible: true,
    });
  }

  return headerElements;
};

interface PageHeaderInterface extends FC<PageHeaderProps> {
  Actions: FC;
  Breadcrumb: FC;
  Illustration: FC<IllustrationProps>;
  UserActions: FC;
  Title: FC;
  State: FC;
  Content: FC;
}

const PageHeader: PageHeaderInterface = ({children, showPlaceholder}) => {
  const {illustration, breadcrumb, title, state, actions, userActions, content} = buildHeaderElements(
    children,
    showPlaceholder
  );

  return (
    <Header>
      <LineContainer>
        {illustration}
        <MainContainer>
          <div>
            <LineContainer>
              {breadcrumb}
              <ActionsContainer>
                {userActions}
                {actions}
              </ActionsContainer>
            </LineContainer>
            <LineContainer>
              {title}
              {state}
            </LineContainer>
            {content}
          </div>
        </MainContainer>
      </LineContainer>
    </Header>
  );
};

PageHeader.Actions = Actions;
PageHeader.Breadcrumb = Breadcrumb;
PageHeader.Illustration = Illustration;
PageHeader.UserActions = UserActions;
PageHeader.Title = Title;
PageHeader.State = State;
PageHeader.Content = Content;

export {PageHeader};
