import React, {useRef, useEffect} from 'react';
import ReactDOM from 'react-dom';
import styled from 'styled-components';
import {
  HashRouter as Router,
  Switch,
  Route,
  Link,
  useLocation
} from 'react-router-dom';

const fetcherRegistry = require('pim/fetcher-registry');
const dateContext = require('pim/date-context');
const userContext = require('pim/user-context');
const securityContext = require('pim/security-context');
const initTranslator = require('pim/init-translator');
const formBuilder = require('pim/form-builder');
const router = require('pim/router');
const $ = require('jquery');
const routeMatcher = require('pim/route-matcher');
const controllerRegistry = require('pim/controller-registry');

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
//      const route = routeMatcher.match.apply(routeMatcher, [`#${location.pathname}`]);
//      const controller = await controllerRegistry.get(route.name);
//  if (controller.class && null !== pageRef.current) {
      if (null !== pageRef.current) {
        router.setRoot(pageRef.current);
        router.defaultRoute(location.pathname);
      }
    })();
  }, [location]);

  return (
    <div ref={pageRef} />
  );
};

const App = ({formBuilder}: {formBuilder: any}) => {
  const menuRef = useRef<HTMLDivElement>(null);
  const containerRef = useRef<HTMLDivElement>(null);

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
      if (null !== menuRef.current) {
        menuRef.current.appendChild(view.el);
        view.render();
      }
    });


    //Backbone.history.start();
  };

  useEffect(() => {
    $(() => {
      initialize();
    });
  }, []);

  return (
    <>
      <Router>
        <div>
          <div id="flash-messages" className="AknDefault-flashContainer">
            <div className="flash-messages-holder AknDefault-flashList"></div>
          </div>
        </div>
        <Container>
          <div ref={menuRef}></div>
          <Content id="container" className="AknDefault-container">
              <Link to='/about'>My link</Link>
              <Switch>
                <Route path="/about">
                  It works
                </Route>
                <Route path="*">
                  <BackboneRouter />
                </Route>
              </Switch>
          </Content>
        </Container>
        <div id="overlay" className="AknOverlay"></div>
        <div data-drop-zone="communication-channel-panel"></div>
      </Router>
    </>
  );
};

setTimeout(async () => {
  // TODO:
  //this.listenTo(mediator, 'pim-app:overlay:show', this.showOverlay);

  // TODO:
  // pim/page-title

  ReactDOM.render(<App formBuilder={formBuilder} />, document.getElementById('app'));
}, 0);
