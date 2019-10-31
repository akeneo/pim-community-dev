/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import * as React from 'react';
import * as ReactDOM from 'react-dom';
import {Provider} from 'react-redux';
import {Store} from 'redux';

import {AttributesMapping as AttributesMappingBackboneModel} from '../../../js/model/attributes-mapping';
import {selectAndFetchFamily} from '../../application/action/family-mapping/family-mapping';
import {FamilyMapping} from '../../application/component/family-mapping/family-mapping';
import {SecurityContext} from '../../application/context/security-context';
import {TranslateContext} from '../../application/context/translate-context';
import {UserContext} from '../../application/context/user-context';
import {FamilyMappingState} from '../../application/reducer/family-mapping';
import {saveFamilyMapping} from '../../application/thunk/save-family';
import {AttributeMappingStatus} from '../../domain/model/attribute-mapping-status.enum';
import {AttributesMapping} from '../../domain/model/attributes-mapping';
import {FamilyMappingStatus} from '../../domain/model/family-mapping-status.enum';
import {configureStore} from '../configure-store';
import {createBackboneConnectorMiddleware} from '../middleware/backbone-connector';
import {isGranted} from '../security';
import {translate} from '../translator';
import {hideLoadingMaskMiddleware} from '../middleware/loading-mask';

import View = require('pimui/js/view/base');

class FamilyMappingView extends View {
  private store: Store;

  public configure() {
    const backboneConnectorMiddleware = createBackboneConnectorMiddleware(this.updateModelFromState.bind(this));
    const loadingMaskMiddleware = hideLoadingMaskMiddleware(this.hideLoadingMask.bind(this));
    this.store = configureStore([backboneConnectorMiddleware, loadingMaskMiddleware]);

    this.listenTo(this.getRoot(), 'save_family_mapping', () => {
      const familyCode = (this.getFormData() as AttributesMappingBackboneModel).familyCode;
      if (!familyCode) {
        return;
      }
      this.store.dispatch<any>(saveFamilyMapping(familyCode, this.store.getState().familyMapping.mapping));
    });

    return super.configure();
  }

  public render() {
    const familyCode = (this.getFormData() as AttributesMappingBackboneModel).familyCode;
    if (!familyCode) {
      return this;
    }

    this.store.dispatch<any>(selectAndFetchFamily(familyCode));

    ReactDOM.render(
      <UserContext.Provider value={{catalogLocale: 'en_US', uiLocale: 'en_US'}}>
        <TranslateContext.Provider value={translate}>
          <SecurityContext.Provider value={isGranted}>
            <Provider store={this.store}>
              <FamilyMapping />
            </Provider>
          </SecurityContext.Provider>
        </TranslateContext.Provider>
      </UserContext.Provider>,
      this.el
    );

    return this;
  }

  public remove() {
    ReactDOM.unmountComponentAtNode(this.el);

    return super.remove();
  }

  private updateModelFromState(state: FamilyMappingState) {
    const data: AttributesMappingBackboneModel = this.getFormData() as AttributesMappingBackboneModel;
    const newData: AttributesMappingBackboneModel = {
      ...data,
      familyMappingStatus: getFamilyMappingStatus(state.familyMapping.mapping),
      hasUnsavedChanges: getHasUnsavedChanges(state.familyMapping.originalMapping, state.familyMapping.mapping),
      attributeCount: getAttributeCount(state.familyMapping.mapping),
      mappedAttributeCount: getMappedAttributeCount(state.familyMapping.mapping)
    };
    this.getFormModel().set(newData);
    this.getRoot().trigger('pim_enrich:form:entity:post_update');
  }

  private hideLoadingMask(): void {
    this.getRoot().trigger('family_mapping_saved');
  }
}

function getFamilyMappingStatus(mapping: AttributesMapping): FamilyMappingStatus {
  if (0 === Object.keys(mapping).length) {
    return FamilyMappingStatus.EMPTY;
  }
  if (undefined !== Object.values(mapping).find(attrMapping => attrMapping.status === AttributeMappingStatus.PENDING)) {
    return FamilyMappingStatus.PENDING;
  }

  return FamilyMappingStatus.FULL;
}

function getHasUnsavedChanges(
  originalMapping: {
    [franklinAttributeCode: string]: {
      attribute: string | null;
      status: AttributeMappingStatus;
    };
  },
  mapping: AttributesMapping
): boolean {
  return (
    undefined !==
    Object.entries(originalMapping).find(([franklinAttributeCode, {attribute, status}]) => {
      if (mapping[franklinAttributeCode].attribute !== attribute) {
        return true;
      }
      if (mapping[franklinAttributeCode].status !== status) {
        return true;
      }
      return false;
    })
  );
}

function getAttributeCount(mapping: AttributesMapping): number {
  return Object.keys(mapping).length;
}

function getMappedAttributeCount(mapping: AttributesMapping): number {
  return Object.values(mapping).filter(attrMapping => attrMapping.status === AttributeMappingStatus.ACTIVE).length;
}

export = FamilyMappingView;
