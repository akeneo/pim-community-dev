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
import {Cell, HeaderCell, Row, Table} from './Table';
import {Scoring} from 'akeneo-design-system';

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
      <Table>
        <Row isHeader={true}>
          <HeaderCell>{translate('akeneo_data_quality_insights.dqi_dashboard.widgets.title')}</HeaderCell>
          <HeaderCell align={'center'} width={48}>
            {translate(`akeneo_data_quality_insights.dqi_dashboard.widgets.score`)}
          </HeaderCell>
          <HeaderCell />
          <HeaderCell />
        </Row>

        {Object.keys(averageScoreByFamilies).length > 0 &&
          Object.entries(averageScoreByFamilies).map(([familyCode, averageScoreRank]: [string, any], index: number) => {
            let family: Family | undefined = undefined;
            if (Object.keys(families).length > 0) {
              family = Object.values(families).find((family: any) => family.code === familyCode);
            }
            return (
              <Row key={index}>
                <Cell highlight={true}>
                  {family && (family.labels[uiLocale] ? family.labels[uiLocale] : '[' + family.code + ']')}
                </Cell>
                <Cell align={'center'}>
                  <Scoring score={averageScoreRank ? Ranks[averageScoreRank] : 'N/A'} />
                </Cell>
                <Cell action={true}>
                  <SeeInGrid
                    follow={() => redirectToProductGridFilteredByFamily(catalogChannel, catalogLocale, familyCode)}
                  />
                </Cell>
                <Cell action={true}>
                  <RemoveItem remove={() => onRemoveFamily(familyCode)} />
                </Cell>
              </Row>
            );
          })}
      </Table>

      {modalElement && createPortal(familyModal, modalElement)}
    </>
  );
};

export default FamilyWidget;
