import React, {FunctionComponent, useEffect, useState} from 'react';
import {createPortal} from 'react-dom';
import useFetchWidgetFamilies from '../../../../infrastructure/hooks/Dashboard/useFetchWidgetFamilies';
import useFetchFamiliesByCodes from '../../../../infrastructure/hooks/Dashboard/useFetchFamiliesByCodes';
import Family from '../../../../domain/Family.interface';
import {Ranks} from '../../../../domain/Rate.interface';
import FamilyModal from './FamilyModal';
import {uniq as _uniq} from 'lodash';
import {redirectToProductGridFilteredByFamily} from '../../../../infrastructure/ProductGridRouter';
import {useTranslate, useUserContext} from '@akeneo-pim-community/legacy-bridge';
import {SeeInGrid} from './SeeInGrid';
import {RemoveItem} from './RemoveItem';
import {AddItem} from './AddItem';
import {QualityScore} from '../../QualityScore';

const MAX_WATCHED_FAMILIES = 20;
const LOCAL_STORAGE_KEY = 'data-quality-insights:dashboard:widgets:families';

interface FamilyWidgetProps {
  catalogLocale: string;
  catalogChannel: string;
}

const FamilyWidget: FunctionComponent<FamilyWidgetProps> = ({catalogChannel, catalogLocale}) => {
  const [modalElement, setModalElement] = useState<HTMLDivElement | null>(null);
  const [showModal, setShowModal] = useState<boolean>(false);
  const [watchedFamilyCodes, setWatchedFamilyCodes] = useState<string[]>([]);
  const [familyCodesToWatch, setFamilyCodesToWatch] = useState<string[]>([]);
  const translate = useTranslate();
  const userContext = useUserContext();

  const averageScoreByFamilies = useFetchWidgetFamilies(catalogChannel, catalogLocale, watchedFamilyCodes);
  const families: Family[] = useFetchFamiliesByCodes(averageScoreByFamilies);

  const uiLocale = userContext.get('uiLocale');

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
    };
  }, []);

  useEffect(() => {
    localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(watchedFamilyCodes));
  }, [watchedFamilyCodes]);

  const header = (
    <div className="AknSubsection-title AknSubsection-title--glued">
      <span>{translate('pim_enrich.entity.family.plural_label')}</span>
      <AddItem add={() => setShowModal(true)}>
        {translate('akeneo_data_quality_insights.dqi_dashboard.widgets.add_families')}
      </AddItem>
    </div>
  );

  const familyModal = (
    <FamilyModal
      onConfirm={onConfirm}
      onDismissModal={onDismissModal}
      onSelectFamily={onSelectFamily}
      isVisible={showModal}
      canAddMoreFamilies={watchedFamilyCodes.length + familyCodesToWatch.length <= MAX_WATCHED_FAMILIES}
      errorMessage={translate('akeneo_data_quality_insights.dqi_dashboard.widgets.family_modal.max_families_msg', {
        count: `${MAX_WATCHED_FAMILIES}`,
      })}
    />
  );

  if (Object.keys(averageScoreByFamilies).length === 0) {
    return (
      <>
        {header}
        <div className="no-family">
          <img src="bundles/pimui/images/illustrations/Family.svg" />
          <p>{translate('akeneo_data_quality_insights.dqi_dashboard.widgets.no_family_helper_msg')}</p>
        </div>
        {modalElement && createPortal(familyModal, modalElement)}
      </>
    );
  }

  return (
    <>
      {header}
      <table className="AknGrid AknGrid--unclickable">
        <tbody className="AknGrid-body">
          <tr>
            <th className="AknGrid-headerCell">
              {translate('akeneo_data_quality_insights.dqi_dashboard.widgets.title')}
            </th>
            <th className="AknGrid-headerCell AknDataQualityInsightsGrid-axis-rate">
              {translate(`akeneo_data_quality_insights.dqi_dashboard.widgets.score`)}
            </th>
            <th className="AknGrid-headerCell AknDataQualityInsightsGrid-axis-rate" />
            <th className="AknGrid-headerCell AknDataQualityInsightsGrid-axis-rate" />
          </tr>

          {Object.keys(averageScoreByFamilies).length > 0 &&
            Object.entries(averageScoreByFamilies).map(
              ([familyCode, averageScoreRank]: [string, any], index: number) => {
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
                      <QualityScore score={averageScoreRank ? Ranks[averageScoreRank] : null} />
                    </td>
                    <td className="AknGrid-bodyCell AknGrid-bodyCell--actions">
                      <SeeInGrid
                        follow={() => redirectToProductGridFilteredByFamily(catalogChannel, catalogLocale, familyCode)}
                      />
                    </td>
                    <td className="AknGrid-bodyCell AknGrid-bodyCell--actions">
                      <RemoveItem remove={() => onRemoveFamily(familyCode)} />
                    </td>
                  </tr>
                );
              }
            )}
        </tbody>
      </table>

      {modalElement && createPortal(familyModal, modalElement)}
    </>
  );
};

export default FamilyWidget;
