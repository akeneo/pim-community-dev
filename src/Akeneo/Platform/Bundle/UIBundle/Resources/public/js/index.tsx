import React, {useRef, useEffect} from 'react';
import ReactDOM from 'react-dom';

const fetcherRegistry = require('pim/fetcher-registry');
const dateContext = require('pim/date-context');
const userContext = require('pim/user-context');
const initTranslator = require('pim/init-translator');
const formBuilder = require('pim/form-builder');
const router = require('pim/router');
const $ = require('jquery');
const Backbone = require('backbone');

const App = ({formBuilder}: {formBuilder: any}) => {
  const menuRef = useRef(null);
  const containerRef = useRef(null);

  const initialize = async () => {
    await Promise.all([fetcherRegistry.initialize(), dateContext.initialize(), userContext.initialize()]);
    router.setRoot(containerRef.current);
    await initTranslator.fetch();

    formBuilder.build('pim-menu').then((view: any) => {
      if (menuRef.current !== null) {
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
      <div ref={menuRef}></div>
      <div ref={containerRef} id="container" className="AknDefault-container"></div>
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
