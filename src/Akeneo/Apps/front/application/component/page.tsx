import * as React from 'react';
import {PropsWithChildren} from 'react';

type Props = PropsWithChildren<{}>;

export const Page = ({children}: Props) => (
  <div className="AknDefault-contentWithColumn">
    <div className="AknDefault-thirdColumnContainer">
      <div className="AknDefault-thirdColumn" />
    </div>

    <div className="AknDefault-contentWithBottom">
      <div className="AknDefault-mainContent">
        <header className="AknTitleContainer">
          <div className="AknTitleContainer-line">
            <div className="AknTitleContainer-mainContainer">
              <div className="AknTitleContainer-line">
                <div className="AknTitleContainer-breadcrumbs"></div>
                <div className="AknTitleContainer-buttonsContainer"></div>
              </div>

              <div className="AknTitleContainer-line">
                <div className="AknTitleContainer-title" />
                <div className="AknTitleContainer-state" />
              </div>

              <div className="AknTitleContainer-line">
                <div className="AknTitleContainer-context AknButtonList" />
              </div>

              <div className="AknTitleContainer-line">
                <div className="AknTitleContainer-meta AknButtonList" />
              </div>
            </div>
          </div>

          <div className="AknTitleContainer-line">
            <div className="AknTitleContainer-navigation" />
          </div>

          <div className="AknTitleContainer-line">
            <div className="AknTitleContainer-search" />
          </div>
        </header>

        {children}
      </div>
    </div>
  </div>
);
