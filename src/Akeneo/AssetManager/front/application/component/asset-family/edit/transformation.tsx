import React from 'react';
import {connect, useDispatch} from 'react-redux';
import {JsonEditor as Editor} from 'jsoneditor-react';
import 'jsoneditor-react/es/editor.min.css';
import {Link, Button, Helper, SectionTitle} from 'akeneo-design-system';
import {
  PageContent,
  PageHeader,
  Section,
  UnsavedChanges,
  useSecurity,
  useTranslate,
  ValidationError,
} from '@akeneo-pim-community/shared';
import {AssetFamilyBreadcrumb} from 'akeneoassetmanager/application/component/app/breadcrumb';
import {AssetFamily, getAssetFamilyLabel} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import {TransformationCollection} from 'akeneoassetmanager/domain/model/asset-family/transformation';
import {
  assetFamilyTransformationsUpdated,
  saveAssetFamily,
} from 'akeneoassetmanager/application/action/asset-family/edit';
import {launchComputeTransformations} from 'akeneoassetmanager/application/action/asset-family/transformation';
import {canEditAssetFamily} from 'akeneoassetmanager/application/reducer/right';
import Ajv from 'ajv';
import {getErrorsView} from 'akeneoassetmanager/application/component/app/validation-error';
import {EditionFormState} from 'akeneoassetmanager/application/reducer/asset-family/edit/form';
import schema from 'akeneoassetmanager/infrastructure/model/asset-family/transformations.schema.json';
import {UserNavigation} from 'akeneoassetmanager/application/component/app/user-navigation';
import {useAssetFamilyFetcher} from 'akeneoassetmanager/infrastructure/fetcher/useAssetFamilyFetcher';

const ajv = new Ajv({allErrors: true, verbose: true});

interface StateProps {
  form: EditionFormState;
  assetFamily: AssetFamily;
  context: {
    locale: string;
  };
  errors: ValidationError[];
  rights: {
    assetFamily: {
      edit: boolean;
    };
  };
}

type AssetFamilyTransformationEditorProps = {
  transformations: TransformationCollection;
  errors: ValidationError[];
  onAssetFamilyTransformationsChange: (transformations: TransformationCollection) => void;
  editMode: boolean;
};

const AssetFamilyTransformationEditor = ({
  transformations,
  errors,
  onAssetFamilyTransformationsChange,
  editMode,
}: AssetFamilyTransformationEditorProps) => {
  //https://github.com/vankop/jsoneditor-react/blob/HEAD/src/Editor.jsx
  if (!editMode) {
    return (
      <div className="AknJsonEditor">
        <Editor value={transformations} mode="view" />
      </div>
    );
  }

  return (
    <div className="AknJsonEditor">
      <Editor
        value={transformations}
        onChange={(event: object) => {
          onAssetFamilyTransformationsChange(null === event ? [] : (event as TransformationCollection));
        }}
        mode="code"
        schema={schema}
        ajv={ajv}
      />
      {getErrorsView(errors, 'transformations', (field: string) => (error: ValidationError) =>
        error.propertyPath.indexOf(field) === 0
      )}
    </div>
  );
};

interface DispatchProps {
  events: {
    onAssetFamilyTransformationsUpdated: (transformations: TransformationCollection) => void;
    onLaunchComputeTransformations: () => void;
  };
}

const Transformation = ({assetFamily, context, events, rights, form, errors}: StateProps & DispatchProps) => {
  const translate = useTranslate();
  const {isGranted} = useSecurity();
  const dispatch = useDispatch();
  const assetFamilyFetcher = useAssetFamilyFetcher();
  const assetFamilyLabel = getAssetFamilyLabel(assetFamily, context.locale);

  const canEditTransformations =
    rights.assetFamily.edit &&
    isGranted('akeneo_assetmanager_asset_family_edit') &&
    isGranted('akeneo_assetmanager_asset_family_manage_transformation');

  return (
    <>
      <PageHeader>
        <PageHeader.Breadcrumb>
          <AssetFamilyBreadcrumb assetFamilyLabel={assetFamilyLabel} />
        </PageHeader.Breadcrumb>
        <PageHeader.UserActions>
          <UserNavigation />
        </PageHeader.UserActions>
        <PageHeader.Actions>
          <Button ghost={true} level="tertiary" onClick={events.onLaunchComputeTransformations}>
            {translate('pim_asset_manager.asset.button.launch_transformations')}
          </Button>
          {canEditTransformations && (
            <Button onClick={() => dispatch(saveAssetFamily(assetFamilyFetcher))}>
              {translate('pim_asset_manager.asset_family.button.save')}
            </Button>
          )}
        </PageHeader.Actions>
        <PageHeader.State>{form.state.isDirty && <UnsavedChanges />}</PageHeader.State>
        <PageHeader.Title>{translate('pim_asset_manager.asset_family.tab.transformations')}</PageHeader.Title>
      </PageHeader>
      <PageContent>
        <Section>
          <div>
            <SectionTitle sticky={0}>
              <SectionTitle.Title>
                {translate('pim_asset_manager.asset_family.transformations.subsection')}
              </SectionTitle.Title>
            </SectionTitle>
            <Helper>
              {translate('pim_asset_manager.asset_family.transformations.help.description')}
              <Link href="https://help.akeneo.com/pim/serenity/articles/assets-transformation.html" target="_blank">
                &nbsp;
                {translate('pim_asset_manager.asset_family.transformations.help.link')}
              </Link>
            </Helper>
          </div>
          <AssetFamilyTransformationEditor
            transformations={assetFamily.transformations}
            errors={errors}
            onAssetFamilyTransformationsChange={events.onAssetFamilyTransformationsUpdated}
            editMode={canEditTransformations}
          />
        </Section>
      </PageContent>
    </>
  );
};

export default connect(
  (state: EditState): StateProps => {
    return {
      form: state.form,
      assetFamily: state.form.data,
      context: {
        locale: state.user.catalogLocale,
      },
      errors: state.form.errors,
      rights: {
        assetFamily: {
          edit: canEditAssetFamily(state.right.assetFamily, state.form.data.identifier),
        },
      },
    };
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        onAssetFamilyTransformationsUpdated: (transformations: TransformationCollection) => {
          dispatch(assetFamilyTransformationsUpdated(transformations));
        },
        onLaunchComputeTransformations: () => {
          dispatch(launchComputeTransformations());
        },
      },
    };
  }
)(Transformation);
