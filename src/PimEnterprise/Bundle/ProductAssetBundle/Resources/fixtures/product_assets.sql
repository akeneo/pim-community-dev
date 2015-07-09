-- INSERT INTO pimee_product_asset_asset (code, description, is_enabled, end_of_use_at, created_at, updated_at) VALUES
-- ('AC1237',	'Front of super hero t-shirt.',	1,	NULL,	'2015-06-08 15:31:33',	'2015-06-08 15:31:33'),
-- ('AC2230',	'Back of super hero t-shirt.',	0,	NULL,	'2015-06-08 15:31:33',	'2015-06-08 15:31:33'),
-- ('AC8600',	'Conception schema of blue jean 860 edition.',	1,	NULL,	'2015-06-08 15:31:33',	'2015-06-08 15:31:33'),
-- ('AC9887',	'Sportwear jean, flex textile. Bottom view.',	0,	'2029-06-08 15:31:33',	'2015-06-08 15:31:33',	'2015-06-08 15:31:33'),
-- ('AC9856',	'Golden jacket, credits belong to NYC Shooting Corp. ',	1,	'2030-06-08 15:31:33',	'2015-06-08 15:31:33',	'2015-06-08 15:31:33'),
-- ('AC1147',	'',	0,	NULL,	'2015-06-08 15:34:11',	'2015-06-08 15:34:11'),
-- ('AC6667',	'',	1,	NULL,	'2015-06-08 15:34:37',	'2015-06-08 15:34:37'),
-- ('AC8747',	'Black t-shirt male in a street.',	1,	NULL,	'2015-06-08 15:35:02',	'2015-06-08 15:35:02'),
-- ('AC8897',	'Yellow t-shirt female, on a boat.',	1,	NULL,	'2015-06-08 15:35:21',	'2015-06-08 15:35:21'),
-- ('AC9969',	'Outdated jean collection. Printed patterns on a silk jean.\r\n',	0,	'2007-06-08 15:35:46',	'2015-06-08 15:35:46',	'2015-06-08 15:35:46'),
-- ('AC6656',	'Transparent t-shirt, XXL, UnNamed brand.',	1,	NULL,	'2015-06-08 15:35:59',	'2015-06-08 15:35:59');

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
