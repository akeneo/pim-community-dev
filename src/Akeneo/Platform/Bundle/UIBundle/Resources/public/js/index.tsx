import React, {useRef, useEffect, useState} from 'react';
import ReactDOM from 'react-dom';
import styled, {ThemeProvider} from 'styled-components';
import {HashRouter as Router, Switch, Route, useLocation} from 'react-router-dom';
import {DependenciesProvider, PimView} from '@akeneo-pim-community/legacy-bridge';
import {UnsavedChangesContext} from 'akeneomeasure/context/unsaved-changes-context';
import {pimTheme} from 'akeneo-design-system';
import {Index as Measurements} from 'akeneomeasure';

const fetcherRegistry = require('pim/fetcher-registry');
const dateContext = require('pim/date-context');
const userContext = require('pim/user-context');
const securityContext = require('pim/security-context');
const initTranslator = require('pim/init-translator');
const formBuilder = require('pim/form-builder');
const router = require('pim/router');
const $ = require('jquery');

//needed to have require available in twig files
require('require-polyfill');

//TODO: remove later as we should be able to not use them anymore
require('jquery-ui');
require('bootstrap');

// Style fix
const Container = styled.div`
  display: flex;
  height: 100vh;
`;

const Content = styled.div`
  flex: 1;
  height: 100vh;
`;

const BackboneRouter = () => {
  const location = useLocation();
  const pageRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    (async () => {
      if (null !== pageRef.current) {
        router.setRoot(pageRef.current);
        router.defaultRoute(location.pathname);
      }
    })();
  }, [location]);

  return <div ref={pageRef} />;
};

const unsavedChanges = {
  hasUnsavedChanges: false,
  setHasUnsavedChanges: (newValue: boolean) => {
    unsavedChanges.hasUnsavedChanges = newValue;
  },
};

const App = () => {
  const menuRef = useRef<HTMLDivElement>(null);
  const containerRef = useRef<HTMLDivElement>(null);
  const [isInialized, setInitialized] = useState(false);

  const initialize = async () => {
    await Promise.all([
      fetcherRegistry.initialize(),
      dateContext.initialize(),
      userContext.initialize(),
      securityContext.initialize(),
    ]);
    router.setRoot(containerRef.current);
    await initTranslator.fetch();
    formBuilder.build('pim-menu').then((view: any) => {
      setInitialized(true);
      if (null !== menuRef.current) {
        menuRef.current.appendChild(view.el);
        view.render();
      }
    });

  };

  useEffect(() => {
    $(() => {
      initialize();
    });
  }, []);

  // Should display loading
  if (!isInialized) return null;

  return (
    <div>
      <Router>
        <div>
          <div id="flash-messages" className="AknDefault-flashContainer">
            <div className="flash-messages-holder AknDefault-flashList"></div>
          </div>
        </div>
        <Container>
          <DependenciesProvider>
            <UnsavedChangesContext.Provider value={unsavedChanges}>
              <ThemeProvider theme={pimTheme}>
                <div ref={menuRef}></div>
                <Content id="container" className="AknDefault-container">
                  <Switch>
                    <Route path="/configuration/measurement">
                      <Measurements />
                    </Route>
                    <Route path="*">
                      <BackboneRouter />
                    </Route>
                  </Switch>
                </Content>
              </ThemeProvider>
            </UnsavedChangesContext.Provider>
          </DependenciesProvider>
        </Container>
        <div id="overlay" className="AknOverlay"></div>
        <div data-drop-zone="communication-channel-panel"></div>
      </Router>
    </div>
  );
};

setTimeout(() => {
  // TODO:
  //this.listenTo(mediator, 'pim-app:overlay:show', this.showOverlay);

  // TODO:
  // pim/page-title

  ReactDOM.render(<App />, document.getElementById('app'));
}, 0);
