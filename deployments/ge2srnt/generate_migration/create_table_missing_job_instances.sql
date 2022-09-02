drop table if exists akeneo_pim_ee.ee_job_instance;
create table akeneo_pim_ee.ee_job_instance as
select
    ee.code,
    ee.label,
    ee.job_name,
    ee.status,
    ee.connector,
    ee.raw_parameters,
    ee.type
from  akeneo_pim_ee.akeneo_batch_job_instance ee
          left join akeneo_pim_ge.akeneo_batch_job_instance ge on ge.code = ee.code
where ge.code is null;
