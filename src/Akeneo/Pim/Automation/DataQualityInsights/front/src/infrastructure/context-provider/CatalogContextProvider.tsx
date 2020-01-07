import React, {FunctionComponent, useEffect} from 'react';
import {useDispatch} from "react-redux";

import {changeCatalogContextLocale, changeCatalogContextChannel, initializeCatalogContext} from "../reducer";

interface LocaleEvent {
  locale: string;
  context: string
}

interface ChannelEvent {
  channel: string;
  context: string;
}

interface CatalogContextProviderProps {
  catalogChannel: string;
  catalogLocale: string;
}

export const CATALOG_CONTEXT_LOCALE_CHANGED = 'data-quality:catalog-context:locale:changed';
export const CATALOG_CONTEXT_CHANNEL_CHANGED = 'data-quality:catalog-context:channel:changed';

const CatalogContextProvider: FunctionComponent<CatalogContextProviderProps> = ({catalogChannel, catalogLocale}) => {
  const dispatchAction = useDispatch();

  useEffect(() => {
    window.addEventListener(CATALOG_CONTEXT_LOCALE_CHANGED, ((event: CustomEvent<LocaleEvent>) => {
      dispatchAction(changeCatalogContextLocale(event.detail.locale));
    }) as EventListener);

    window.addEventListener(CATALOG_CONTEXT_CHANNEL_CHANGED, ((event: CustomEvent<ChannelEvent>) => {
      dispatchAction(changeCatalogContextChannel(event.detail.channel));
    }) as EventListener);

    dispatchAction(initializeCatalogContext(catalogChannel, catalogLocale));
  }, []);

  return (
    <></>
  )
};

export default CatalogContextProvider;
