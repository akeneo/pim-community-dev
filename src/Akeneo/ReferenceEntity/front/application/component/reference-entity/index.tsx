import React from 'react';
import {connect} from 'react-redux';
import {Breadcrumb} from 'akeneo-design-system';
import {PimView} from '@akeneo-pim-community/legacy-bridge';
import __ from 'akeneoreferenceentity/tools/translator';
import Table from 'akeneoreferenceentity/application/component/reference-entity/index/table';
import ReferenceEntity from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import {redirectToReferenceEntity} from 'akeneoreferenceentity/application/action/reference-entity/router';
import {IndexState} from 'akeneoreferenceentity/application/reducer/reference-entity/index';
import {referenceEntityCreationStart} from 'akeneoreferenceentity/domain/event/reference-entity/create';
import CreateReferenceEntityModal from 'akeneoreferenceentity/application/component/reference-entity/create';
const securityContext = require('pim/security-context');

interface StateProps {
  context: {
    locale: string;
  };

  grid: {
    referenceEntities: ReferenceEntity[];
    matchesCount: number;
    isLoading: boolean;
  };

  create: {
    active: boolean;
  };

  acls: {
    create: boolean;
  };
}

interface DispatchProps {
  events: {
    onRedirectToReferenceEntity: (referenceEntity: ReferenceEntity) => void;
    onCreationStart: () => void;
  };
}
class ReferenceEntityListView extends React.Component<StateProps & DispatchProps> {
  private createButton: HTMLButtonElement;

  componentDidMount() {
    if (this.createButton) {
      this.createButton.focus();
    }
  }

  render() {
    const {grid, context, events, create, acls} = this.props;

    return (
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
                    <div className="AknTitleContainer-breadcrumbs">
                      <Breadcrumb>
                        <Breadcrumb.Step>{__('pim_reference_entity.reference_entity.breadcrumb')}</Breadcrumb.Step>
                      </Breadcrumb>
                    </div>
                    <div className="AknTitleContainer-buttonsContainer">
                      <PimView
                        className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
                        viewName="pim-reference-entity-index-user-navigation"
                      />
                      {acls.create ? (
                        <div className="AknTitleContainer-actionsContainer AknButtonList">
                          <button
                            type="button"
                            ref={(button: HTMLButtonElement) => {
                              this.createButton = button;
                            }}
                            className="AknButton AknButton--apply AknButtonList-item"
                            onClick={events.onCreationStart}
                          >
                            {__('pim_reference_entity.reference_entity.button.create')}
                          </button>
                        </div>
                      ) : null}
                    </div>
                  </div>
                  <div className="AknTitleContainer-line">
                    {grid.isLoading === false && grid.referenceEntities.length === 0 ? (
                      <div className="AknDescriptionHeader AknDescriptionHeader--sticky AknDescriptionHeader--push">
                        <div
                          className="AknDescriptionHeader-icon"
                          style={{backgroundImage: 'url("/bundles/pimui/images/illustrations/Reference-entities.svg")'}}
                        />
                        <div className="AknDescriptionHeader-title">
                          {__('pim_reference_entity.reference_entity.index.grid.help.title')}
                          <div className="AknDescriptionHeader-description">
                            {__('pim_reference_entity.reference_entity.index.grid.help.description_part_one')} <br />
                            {__('pim_reference_entity.reference_entity.index.grid.help.description_part_two')} <br />
                            {__('pim_reference_entity.reference_entity.index.grid.help.description_part_three')} <br />
                            {__('pim_reference_entity.reference_entity.index.grid.help.description_part_four')} <br />
                            <a href="https://help.akeneo.com/pim/articles/what-about-reference-entities.html?utm_source=akeneo-app&utm_medium=ref-entities-grid">
                              {__('pim_reference_entity.reference_entity.index.grid.help.description_part_five')}
                            </a>
                            <br />
                          </div>
                        </div>
                      </div>
                    ) : (
                      <div className="AknTitleContainer-title">
                        <span className={grid.isLoading ? 'AknLoadingPlaceHolder' : ''}>
                          {__(
                            'pim_reference_entity.reference_entity.index.grid.count',
                            {
                              count: grid.referenceEntities.length,
                            },
                            grid.referenceEntities.length
                          )}
                        </span>
                      </div>
                    )}
                    <div className="AknTitleContainer-state" />
                  </div>
                </div>
                <div>
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
            <div className="AknGrid--gallery">
              <div className="AknGridContainer">
                <Table
                  onRedirectToReferenceEntity={events.onRedirectToReferenceEntity}
                  locale={context.locale}
                  referenceEntities={grid.referenceEntities}
                  isLoading={grid.isLoading}
                />
              </div>
            </div>
          </div>
        </div>
        {create.active ? <CreateReferenceEntityModal /> : null}
      </div>
    );
  }
}

export default connect(
  (state: IndexState): StateProps => {
    const locale = undefined === state.user || undefined === state.user.catalogLocale ? '' : state.user.catalogLocale;
    const referenceEntities = undefined === state.grid || undefined === state.grid.items ? [] : state.grid.items;
    const matchesCount =
      undefined === state.grid || undefined === state.grid.matchesCount ? 0 : state.grid.matchesCount;

    return {
      context: {
        locale,
      },
      grid: {
        referenceEntities,
        matchesCount,
        isLoading: state.grid.isFetching && state.grid.items.length === 0,
      },
      create: {
        active: state.create.active,
      },
      acls: {
        create: securityContext.isGranted('akeneo_referenceentity_reference_entity_create'),
      },
    };
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        onRedirectToReferenceEntity: (referenceEntityCreation: ReferenceEntity) => {
          dispatch(redirectToReferenceEntity(referenceEntityCreation.getIdentifier(), 'record'));
        },
        onCreationStart: () => {
          dispatch(referenceEntityCreationStart());
        },
      },
    };
  }
)(ReferenceEntityListView);
