import React, {FunctionComponent, useEffect} from 'react';
import {useDispatch} from 'react-redux';

import {ATTRIBUTE_EDIT_FORM_TAB_CHANGED_EVENT} from '../../constant';
import {changeAttributeEditFormTabAction} from '../../../infrastructure/reducer/AttributeEditForm/pageContextReducer';

interface TabEvent {
  currentTab: string;
}

const CURRENT_TAB_SESSION_KEY = 'current_form_tab';

interface PageContextListenerProps {}

const PageContextListener: FunctionComponent<PageContextListenerProps> = () => {
  const dispatchAction = useDispatch();

  useEffect(() => {
    const handleTabChanged = (event: CustomEvent<TabEvent>) => {
      dispatchAction(changeAttributeEditFormTabAction(event.detail.currentTab));
    };
    window.addEventListener(ATTRIBUTE_EDIT_FORM_TAB_CHANGED_EVENT, handleTabChanged as EventListener);

    dispatchAction(changeAttributeEditFormTabAction(sessionStorage.getItem(CURRENT_TAB_SESSION_KEY)));

    return () => {
      window.removeEventListener(ATTRIBUTE_EDIT_FORM_TAB_CHANGED_EVENT, handleTabChanged as EventListener);
    };
  }, []);

  return <></>;
};

export default PageContextListener;
