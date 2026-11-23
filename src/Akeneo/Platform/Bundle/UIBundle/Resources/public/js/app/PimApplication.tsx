import React, {useEffect} from 'react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ApplicationZones} from './bootstrap';
import {LegacyZone} from './LegacyZone';
import {Overlay} from './Overlay';
import {runIsolated} from './runIsolated';

type PimApplicationProps = {
  zones: ApplicationZones;
  onReady: () => void;
};

/**
 * Container of the pages. Its content is owned by the router (pim/router), React must never render children in it.
 */
const PageContainer = React.memo(() => <div id="container" className="AknDefault-container" />);

/**
 * Root of the application: the layout, the shell extensions (menu, communication panel...) and the page container.
 * `onReady` is called once everything is in the DOM, to start the router.
 */
const PimApplication = ({zones, onReady}: PimApplicationProps) => {
  useEffect(() => {
    // Effects of the children (rendering of the shell zones) run before this one. A page failing to render (the first
    // route is rendered synchronously) must not unmount the application.
    runIsolated(onReady);
  }, []);

  return (
    <ThemeProvider theme={pimTheme}>
      <DependenciesProvider>
        <div id="page" className="AknDefault-page">
          <LegacyZone code="menu" views={zones['menu'] || []} />
          <PageContainer />
          <Overlay />
          <LegacyZone code="communication-channel-panel" views={zones['communication-channel-panel'] || []} />
          <div id="flash-messages" />
        </div>
      </DependenciesProvider>
    </ThemeProvider>
  );
};

export {PimApplication};
