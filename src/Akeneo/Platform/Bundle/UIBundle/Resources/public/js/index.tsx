import React, {useRef, useEffect} from 'react';
import ReactDOM from 'react-dom';
import styled from 'styled-components';

const fetcherRegistry = require('pim/fetcher-registry');
const dateContext = require('pim/date-context');
const userContext = require('pim/user-context');
const initTranslator = require('pim/init-translator');
const formBuilder = require('pim/form-builder');
const router = require('pim/router');
const $ = require('jquery');
const Backbone = require('backbone');

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

const App = ({formBuilder}: {formBuilder: any}) => {
  const menuRef = useRef<HTMLDivElement>(null);
  const containerRef = useRef<HTMLDivElement>(null);

  const initialize = async () => {
    await Promise.all([fetcherRegistry.initialize(), dateContext.initialize(), userContext.initialize()]);
    router.setRoot(containerRef.current);
    await initTranslator.fetch();

    formBuilder.build('pim-menu').then((view: any) => {
      if (null !== menuRef.current) {
        menuRef.current.appendChild(view.el);
        view.render();
      }
    });

    Backbone.history.start();
  };

  useEffect(() => {
    $(() => {
      initialize();
    });
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
        <Content ref={containerRef} id="container" className="AknDefault-container"></Content>
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
