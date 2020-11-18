import React, {useRef, useEffect} from 'react';
import ReactDOM from 'react-dom';
const fetcherRegistry = require('pim/fetcher-registry');
const dateContext = require('pim/date-context');
const userContext = require('pim/user-context');
const initTranslator = require('pim/init-translator');
const formBuilder = require('pim/form-builder')

const App = ({formBuilder}: {formBuilder: any}) => {
  const menuRef = useRef(null);

  useEffect(() => {
    formBuilder.build('pim-menu').then((view: any) => {
      if (menuRef.current !== null) {
        menuRef.current.appendChild(view.el);
        view.render();
      }
    })
  }, [])

  return (
    <>
      <div>
        <div id="flash-messages" className="AknDefault-flashContainer">
          <div className="flash-messages-holder AknDefault-flashList"></div>
        </div>
      </div>
      <div ref={menuRef}></div>
      <div id="container" className="AknDefault-container"></div>
      <div id="overlay" className="AknOverlay"></div>
      <div data-drop-zone="communication-channel-panel"></div>
    </>
  )
}

setTimeout(async () => {
  // TODO:
  //this.listenTo(mediator, 'pim-app:overlay:show', this.showOverlay);
  await Promise.all([fetcherRegistry.initialize(), dateContext.initialize(), userContext.initialize()]);
  await initTranslator.fetch();

  // TODO:
  // pim/page-title

  ReactDOM.render((
  <App formBuilder={formBuilder}/>), document.getElementById('app'));
}, 0);
