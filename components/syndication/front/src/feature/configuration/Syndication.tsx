import React, {useCallback, useEffect, useState} from 'react';
import {
  NotificationLevel,
  useNotify,
  useRoute,
  useRouter,
  useTranslate,
  ValidationError,
} from '@akeneo-pim-community/shared';
import {EntityTypeValue} from './contexts/EntityTypeContext';
import {PlatformConfigurator} from './PlatformConfigurator';
import {PlatformConfiguration} from './models/PlatformConfiguration';
import {Platform} from './models/Platform';

type JobConfiguration = {
  code: string;
  configuration: PlatformConfiguration;
};

const Syndication = ({jobCode}: {jobCode: string}) => {
  const [jobConfiguration, setJobConfiguration] = useState<JobConfiguration | null>(null);
  const [platform, setPlatform] = useState<Platform | null>(null);
  const [entityType, setEntityType] = useState<EntityTypeValue>('product');
  const [validationErrors, setValidationErrors] = useState<ValidationError[]>([]);
  const getJobRoute = useRoute('pim_enrich_job_instance_rest_export_get', {identifier: jobCode});
  const saveJobRoute = useRoute('pim_enrich_job_instance_rest_export_put', {identifier: jobCode});
  const router = useRouter();
  const notify = useNotify();
  const translate = useTranslate();

  const saveJobConfiguration = async () => {
    setValidationErrors([]);
    const response = await fetch(saveJobRoute, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({...jobConfiguration, connector: undefined}),
    });

    if (!response.ok) {
      setValidationErrors([]);

      try {
        const json = await response.json();
        setValidationErrors(json.normalized_errors);
      } catch (error) {}

      notify(NotificationLevel.ERROR, translate('pim_import_export.entity.job_instance.flash.update.fail'));
    } else {
      notify(NotificationLevel.SUCCESS, translate('pim_import_export.entity.job_instance.flash.update.success'));
    }
  };

  const handleConfigurationChange = useCallback(
    (configuration: PlatformConfiguration) => {
      setJobConfiguration((jobConfiguration): JobConfiguration => {
        if (null === jobConfiguration) return {code: jobCode, configuration};

        return {
          ...jobConfiguration,
          configuration,
        };
      });
    },
    [jobCode]
  );

  useEffect(() => {
    const fetchJobConfiguration = async () => {
      const response = await fetch(getJobRoute, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      if (404 === response.status) {
        throw new Error(`Job with code "${jobCode}" does not exist`);
      }

      const jobConfiguration = await response.json();

      setJobConfiguration(jobConfiguration);
      setEntityType(['syndication_product_export'].includes(jobConfiguration.job_name) ? 'product' : 'product_model');
    };

    fetchJobConfiguration();
  }, [getJobRoute, jobCode]);

  const platformCode = jobConfiguration?.configuration?.connection?.connectedChannelCode ?? null;
  useEffect(() => {
    if (null === platformCode || '' === platformCode) return;

    const fetchPlatform = async () => {
      const getPlatformRoute = router.generate('pimee_syndication_get_platform_action', {platformCode});
      const response = await fetch(getPlatformRoute, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      if (404 === response.status) {
        throw new Error('Cannot fetch platform configuration');
      }

      const platform = await response.json();
      setPlatform(platform);
    };

    fetchPlatform();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [router, platformCode]);

  if (null === jobConfiguration || null === platform) return null;

  return (
    <PlatformConfigurator
      code={jobConfiguration.code}
      configuration={jobConfiguration.configuration}
      platform={platform}
      entityType={entityType}
      validationErrors={validationErrors}
      onSave={saveJobConfiguration}
      onConfigurationChange={handleConfigurationChange}
    />
  );
};

export {Syndication};
