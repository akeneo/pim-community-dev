INSERT INTO pimee_product_asset_asset_tag (asset_id, tag_id)
  SELECT pimee_product_asset_asset.id, pimee_product_asset_tag.id
  FROM pimee_product_asset_asset
  CROSS JOIN pimee_product_asset_tag;

DELETE j FROM pimee_product_asset_asset_tag j
INNER JOIN pimee_product_asset_asset a ON (a.id = j.asset_id)
WHERE a.code = 'photo';

DELETE j FROM pimee_product_asset_asset_tag j
INNER JOIN pimee_product_asset_asset a ON (a.id = j.asset_id)
INNER JOIN pimee_product_asset_tag t ON (t.id = j.tag_id)
WHERE a.code <> 'mouette'
AND t.code = 'women';

DELETE j FROM pimee_product_asset_asset_tag j
INNER JOIN pimee_product_asset_asset a ON (a.id = j.asset_id)
INNER JOIN pimee_product_asset_tag t ON (t.id = j.tag_id)
WHERE a.code NOT IN ('eagle', 'minivan')
AND t.code IN ('lacework', 'men');

-- Asset categories

INSERT INTO pimee_product_asset_asset_category (asset_id, category_id)
  SELECT pimee_product_asset_asset.id, pimee_product_asset_category.id
  FROM pimee_product_asset_asset
  CROSS JOIN pimee_product_asset_category;

DELETE j FROM pimee_product_asset_asset_category j
INNER JOIN pimee_product_asset_asset a ON (a.id = j.asset_id)
INNER JOIN pimee_product_asset_category c ON (c.id = j.category_id)
WHERE c.code NOT IN ('images', 'autre', 'situ');

DELETE j FROM pimee_product_asset_asset_category j
INNER JOIN pimee_product_asset_asset a ON (a.id = j.asset_id)
INNER JOIN pimee_product_asset_category c ON (c.id = j.category_id)
WHERE a.code NOT IN ('paint', 'chicagoskyline', 'akene', 'autumn', 'bridge')
AND c.code = 'images';

DELETE j FROM pimee_product_asset_asset_category j
INNER JOIN pimee_product_asset_asset a ON (a.id = j.asset_id)
INNER JOIN pimee_product_asset_category c ON (c.id = j.category_id)
WHERE a.code NOT IN ('autumn', 'bridge', 'dog', 'eagle', 'machine')
AND c.code = 'autre';

DELETE j FROM pimee_product_asset_asset_category j
INNER JOIN pimee_product_asset_asset a ON (a.id = j.asset_id)
INNER JOIN pimee_product_asset_category c ON (c.id = j.category_id)
WHERE a.code NOT IN ('paint', 'man-wall', 'minivan', 'mouette', 'mountain')
AND c.code = 'situ';
