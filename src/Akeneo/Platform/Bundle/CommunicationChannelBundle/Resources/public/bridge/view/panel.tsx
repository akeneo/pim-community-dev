import React from 'react';
import ReactView from 'akeneocommunicationchannel/bridge/react/react-view';
import {Panel} from 'akeneocommunicationchannel/components/panel';

class PanelView extends ReactView {
  reactElementToMount(): JSX.Element {
    return <Panel />;
  }
  
  render() {
    return super.render();
  }
}

export = PanelView;
