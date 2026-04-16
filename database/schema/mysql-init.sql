CREATE DATABASE IF NOT EXISTS `laravel_primary`;
CREATE DATABASE IF NOT EXISTS `laravel_secondary`;

GRANT ALL PRIVILEGES ON `laravel_primary`.* TO 'sail'@'%';
GRANT ALL PRIVILEGES ON `laravel_secondary`.* TO 'sail'@'%';
