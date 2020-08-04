import React, {FunctionComponent, useEffect, useState} from "react";
import {createPortal} from "react-dom";
import useFetchWidgetFamilies from "../../../../infrastructure/hooks/Dashboard/useFetchWidgetFamilies";
import useFetchFamiliesByCodes from "../../../../infrastructure/hooks/Dashboard/useFetchFamiliesByCodes";
import Family from "../../../../domain/Family.interface";
import {Ranks} from "../../../../domain/Rate.interface";
import Rate from '@akeneo-pim-community/data-quality-insights/src/application/component/Rate';
import FamilyModal from "./FamilyModal";
import {uniq as _uniq} from 'lodash';
import {redirectToProductGridFilteredByFamily} from "../../../../infrastructure/ProductGridRouter";
import {useAxesContext} from "@akeneo-pim-community/data-quality-insights/src/application/context/AxesContext";

const __ = require('oro/translator');
const UserContext = require('pim/user-context');

const MAX_WATCHED_FAMILIES = 20;
const LOCAL_STORAGE_KEY = 'data-quality-insights:dashboard:widgets:families';

interface FamilyWidgetProps {
  catalogLocale: string;
  catalogChannel: string;
}

const FamilyWidget: FunctionComponent<FamilyWidgetProps> = ({catalogChannel, catalogLocale}) => {

  const [modalElement, setModalElement] = useState<HTMLDivElement|null>(null);
  const [showModal, setShowModal] = useState<boolean>(false);
  const [watchedFamilyCodes, setWatchedFamilyCodes] = useState<string[]>([]);
  const [familyCodesToWatch, setFamilyCodesToWatch] = useState<string[]>([]);
  const axesContext = useAxesContext();

  const ratesByFamily = useFetchWidgetFamilies(catalogChannel, catalogLocale, watchedFamilyCodes);
  const families: Family[] = useFetchFamiliesByCodes(ratesByFamily);

  const uiLocale = UserContext.get('uiLocale');

  const onSelectFamily = (jQueryEvent: any) => {
    const selectedFamilies = jQueryEvent.val.filter((familyCode: string) => !watchedFamilyCodes.includes(familyCode));
    setFamilyCodesToWatch(selectedFamilies);
  };

  const onConfirm = () => {
    setWatchedFamilyCodes(_uniq([...watchedFamilyCodes, ...familyCodesToWatch]));
    setFamilyCodesToWatch([]);
    setShowModal(false);
  };

  const onDismissModal = () => {
    setFamilyCodesToWatch([]);
    setShowModal(false);
  };

  const onRemoveFamily = (familyCodeToDelete: string) => {
    const previousFamilyCodes = localStorage.getItem(LOCAL_STORAGE_KEY);
    if (previousFamilyCodes) {
      const familyCodes = JSON.parse(previousFamilyCodes);
      familyCodes.splice(familyCodes.indexOf(familyCodeToDelete), 1);
      setWatchedFamilyCodes([...familyCodes]);
    }
  };

  useEffect(() => {
    const modal = document.createElement('div');
    setModalElement(modal);
    document.body.appendChild(modal);

    const families = localStorage.getItem(LOCAL_STORAGE_KEY);
    if (families) {
      setWatchedFamilyCodes(JSON.parse(families));
    }

    return () => {
      if (modalElement) {
        document.body.removeChild(modalElement);
      }
    }
  }, []);

  useEffect(() => {
    localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(watchedFamilyCodes));
  }, [watchedFamilyCodes]);

  const header = <div className="AknSubsection-title AknSubsection-title--glued">
    <span>{__('pim_enrich.entity.family.plural_label')}</span>
    <div className="AknButton AknButton--micro" onClick={() => setShowModal(true)}>
      {__('akeneo_data_quality_insights.dqi_dashboard.widgets.add_families')}
    </div>
  </div>;

  const familyModal =
    <FamilyModal
      onConfirm={onConfirm}
      onDismissModal={onDismissModal}
      onSelectFamily={onSelectFamily}
      isVisible={showModal}
      canAddMoreFamilies={watchedFamilyCodes.length + familyCodesToWatch.length <= MAX_WATCHED_FAMILIES}
      errorMessage={__('akeneo_data_quality_insights.dqi_dashboard.widgets.family_modal.max_families_msg', {count: MAX_WATCHED_FAMILIES})}
    />;

  if (Object.keys(ratesByFamily).length === 0) {
    return (
      <>
        {header}
        <div className="no-family">
          <img src="bundles/pimui/images/illustrations/Family.svg"/>
          <p>{__('akeneo_data_quality_insights.dqi_dashboard.widgets.no_family_helper_msg')}</p>
        </div>
        {modalElement && createPortal(familyModal, modalElement)}
      </>
    )
  }

  return (
    <>
      {header}
      <table className="AknGrid AknGrid--unclickable">
        <tbody className="AknGrid-body">
          <tr>
            <th className="AknGrid-headerCell">{__('Title')}</th>
            <th className="AknGrid-headerCell AknDataQualityInsightsGrid-axis-rate">{__(`akeneo_data_quality_insights.product_evaluation.axis.enrichment.title`)}</th>
            {axesContext.axes.includes('consistency') &&
              <th className="AknGrid-headerCell AknDataQualityInsightsGrid-axis-rate">{__(`akeneo_data_quality_insights.product_evaluation.axis.consistency.title`)}</th>
            }
            <th className="AknGrid-headerCell AknDataQualityInsightsGrid-axis-rate"> </th>
            <th className="AknGrid-headerCell AknDataQualityInsightsGrid-axis-rate"> </th>
          </tr>

          {Object.keys(ratesByFamily).length > 0 && Object.entries(ratesByFamily).map(([familyCode, ratesByAxis]:[string, any], index: number) => {
            let family: Family | undefined = undefined;
            if (Object.keys(families).length > 0) {
              family = Object.values(families).find((family: any) => family.code === familyCode);
            }
            return (
              <tr key={index} className="AknGrid-bodyRow">
                <td className="AknGrid-bodyCell AknGrid-bodyCell--highlight familyName">
                  {family && (family.labels[uiLocale] ? family.labels[uiLocale] : '[' + family.code + ']')}
                </td>
                <td className="AknGrid-bodyCell AknDataQualityInsightsGrid-axis-rate">
                  <Rate value={ratesByAxis.enrichment ? Ranks[ratesByAxis.enrichment] : null}/>
                </td>
                {axesContext.axes.includes('consistency') &&
                  <td className="AknGrid-bodyCell AknDataQualityInsightsGrid-axis-rate">
                    <Rate value={ratesByAxis.consistency ? Ranks[ratesByAxis.consistency] : null}/>
                  </td>
                }
                <td className="AknGrid-bodyCell AknGrid-bodyCell--actions">
                  <div className="AknButton AknButton--micro" onClick={() => redirectToProductGridFilteredByFamily(catalogChannel, catalogLocale, familyCode)}>
                    {__('akeneo_data_quality_insights.dqi_dashboard.widgets.see_in_grid')}
                  </div>
                </td>
                <td className="AknGrid-bodyCell AknGrid-bodyCell--actions">
                  <img style={{cursor: "pointer"}} width="16" src="/bundles/pimui/images/icon-delete-slategrey.svg" onClick={() => onRemoveFamily(familyCode)}/>
                </td>
              </tr>
            )
          })}
        </tbody>
      </table>

      {modalElement && createPortal(familyModal, modalElement)}
    </>
  )
};

export default FamilyWidget;
