with
    table_column as (
        select
            table_name,
            table_schema,
            column_name,
            data_type,
            is_nullable
        from information_schema.columns
        where table_schema IN ('akeneo_pim_ee', 'akeneo_pim_ge')
    ),
    existing_table_in_ge_and_ee as (
        select distinct ee.table_name
        from table_column ee
            join table_column ce on ee.table_name = ce.table_name
        where
            ee.table_schema IN ('akeneo_pim_ee')
            and ce.table_schema IN ('akeneo_pim_ge')
    )
select * from existing_table_in_ge_and_ee;
