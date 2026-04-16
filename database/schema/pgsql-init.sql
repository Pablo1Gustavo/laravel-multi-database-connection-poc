-- Runs once on fresh pgsql container against POSTGRES_DB (laravel_primary).
-- Both Laravel connections share this single physical database; each operates
-- in its own schema. getDatabaseName() is overridden post-connect (see
-- AppServiceProvider) so the library qualifies cross-connection refs as
-- "schema"."table", which PostgreSQL resolves natively.

CREATE SCHEMA IF NOT EXISTS laravel_primary AUTHORIZATION CURRENT_USER;
CREATE SCHEMA IF NOT EXISTS laravel_secondary AUTHORIZATION CURRENT_USER;
