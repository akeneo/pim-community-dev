insert into pimee_security_locale_access (user_group_id, locale_id, view_products, edit_products)
with
    default_user_group_and_app as (
        select id as user_group_id from oro_access_group where name ='All' or type ='app'
    ),
    locale as (
        select id as locale_id from pim_catalog_locale
    )
select
    default_user_group_and_app.user_group_id,
    locale.locale_id,
    1 as view_products,
    1 as view_products
from default_user_group_and_app,locale
;


insert into pimee_security_attribute_group_access (user_group_id, attribute_group_id, view_attributes, edit_attributes)
with
    default_user_group_and_app as (
        select id as user_group_id from oro_access_group where name ='All' or type ='app'
    ),
    attribute_group as (
        select id as attribute_group_id from pim_catalog_attribute_group
    )
select
    default_user_group_and_app.user_group_id,
    attribute_group.attribute_group_id,
    1 as view_products,
    1 as view_products
from default_user_group_and_app, attribute_group
;


insert into pimee_security_job_profile_access (job_profile_id, user_group_id, execute_job_profile, edit_job_profile)
with
    default_user_group_and_app as (
        select id as user_group_id from oro_access_group where name ='All' or type ='app'
    ),
    job_instance as (
        select id as job_profile_id from akeneo_batch_job_instance
    )
select
    job_instance.job_profile_id,
    default_user_group_and_app.user_group_id,
    1 as execute_job_profile,
    1 as edit_job_profile
from default_user_group_and_app,job_instance
;


insert into pimee_security_product_category_access (user_group_id, category_id, view_items, edit_items, own_items)
with
    default_user_group_and_app as (
        select id as user_group_id from oro_access_group where name ='All' or type ='app'
    ),
    category as (
        select id as category_id from pim_catalog_category
    )
select
    default_user_group_and_app.user_group_id,
    category.category_id,
    1 as view_items,
    1 as edit_items,
    1 as own_items
from default_user_group_and_app,category
;
