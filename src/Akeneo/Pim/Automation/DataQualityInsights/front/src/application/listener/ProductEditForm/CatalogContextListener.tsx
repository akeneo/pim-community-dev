import React, {FunctionComponent, useEffect} from 'react';
import {useDispatch} from 'react-redux';
import {
  changeCatalogContextChannel,
  changeCatalogContextLocale,
  initializeCatalogContext,
} from '../../../infrastructure/reducer';

interface LocaleEvent {
  locale: string;
  context: string;
}

interface ChannelEvent {
  channel: string;
  context: string;
}

interface CatalogContextListenerProps {
  catalogChannel: string;
  catalogLocale: string;
}

export const CATALOG_CONTEXT_LOCALE_CHANGED = 'data-quality:catalog-context:locale:changed';
export const CATALOG_CONTEXT_CHANNEL_CHANGED = 'data-quality:catalog-context:channel:changed';

const CatalogContextListener: FunctionComponent<CatalogContextListenerProps> = ({catalogChannel, catalogLocale}) => {
  const dispatchAction = useDispatch();

  useEffect(() => {
    const handleCatalogLocaleChanged = (event: CustomEvent<LocaleEvent>) => {
      dispatchAction(changeCatalogContextLocale(event.detail.locale));
    };

    const handleCatalogChannelChanged = (event: CustomEvent<ChannelEvent>) => {
      dispatchAction(changeCatalogContextChannel(event.detail.channel));
    };

    window.addEventListener(CATALOG_CONTEXT_LOCALE_CHANGED, handleCatalogLocaleChanged as EventListener);
    window.addEventListener(CATALOG_CONTEXT_CHANNEL_CHANGED, handleCatalogChannelChanged as EventListener);

    dispatchAction(initializeCatalogContext(catalogChannel, catalogLocale));

    return () => {
      window.removeEventListener(CATALOG_CONTEXT_LOCALE_CHANGED, handleCatalogLocaleChanged as EventListener);
      window.removeEventListener(CATALOG_CONTEXT_CHANNEL_CHANGED, handleCatalogChannelChanged as EventListener);
    };
  }, []);

  return <></>;
};

export default CatalogContextListener;
