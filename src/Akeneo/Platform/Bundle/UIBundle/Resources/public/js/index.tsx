import React, {useRef, useEffect, useCallback, useState} from 'react';
import ReactDOM from 'react-dom';
import styled from 'styled-components';

const fetcherRegistry = require('pim/fetcher-registry');
const dateContext = require('pim/date-context');
const userContext = require('pim/user-context');
const securityContext = require('pim/security-context');
const initTranslator = require('pim/init-translator');
const formBuilder = require('pim/form-builder');
const controllerRegistry = require('pim/controller-registry');
const $ = require('jquery');
const routeMatcher = require('pim/route-matcher');
const router = require('pim/router');
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

const Router = ({path}: {path: string}) => {
  const pageRef = useRef<HTMLDivElement>(null);
  let page = null;

  useEffect(() => {
    let cleanedPath = path;
    if (cleanedPath.indexOf('/') !== 0) {
      cleanedPath = '/' + cleanedPath;
    }

    if (cleanedPath.indexOf('|') !== -1) {
      cleanedPath = cleanedPath.substring(0, cleanedPath.indexOf('|'));
    }

    (async () => {
      const route = routeMatcher.match.apply(routeMatcher, [path]);
      const controller = await controllerRegistry.get(route.name);
      if (controller.class && null !== pageRef.current) {
        router.setRoot(pageRef.current);
        router.defaultRoute(path);
      }
    })();
  }, [path]);

  return (
    <Content ref={pageRef} className="AknDefault-container">
      {page}
    </Content>
  );
};

const App = ({formBuilder}: {formBuilder: any}) => {
  const menuRef = useRef<HTMLDivElement>(null);

  const initialize = useCallback(async () => {
    await Promise.all([
      fetcherRegistry.initialize(),
      dateContext.initialize(),
      userContext.initialize(),
      securityContext.initialize(),
    ]);
    await initTranslator.fetch();

    formBuilder.build('pim-menu').then((view: any) => {
      if (null !== menuRef.current) {
        menuRef.current.appendChild(view.el);
        view.render();
      }
    });
  }, []);

  const [hash, setHash] = useState(window.location.hash);
  const hashChange = useCallback(() => {
    setHash(window.location.hash);
  }, []);

  useEffect(() => {
    $(() => {
      initialize();
    });

    window.addEventListener('hashchange', hashChange, false);
    window.addEventListener('popstate', hashChange, false);
  }, []);

  return (
    <>
      <div>
        <div id="flash-messages" className="AknDefault-flashContainer">
          <div className="flash-messages-holder AknDefault-flashList"></div>
        </div>
      </div>
      <Container>
        <div ref={menuRef}></div>
        <Router path={hash} />
      </Container>
      <div id="overlay" className="AknOverlay"></div>
      <div data-drop-zone="communication-channel-panel"></div>
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
