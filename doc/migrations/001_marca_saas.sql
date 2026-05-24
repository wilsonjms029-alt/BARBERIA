-- Marca SaaS: logo y nombre del negocio en configuracion
INSERT INTO `configuracion` (`clave`, `valor`) VALUES
('nombre_negocio', ''),
('logo_url', '')
ON DUPLICATE KEY UPDATE `clave` = `clave`;
