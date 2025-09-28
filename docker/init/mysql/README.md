Insert any .sql script here.
It will be executed at every container startup.

Should be used for making sure databases exist. for example:
```sql
CREATE DATABASE IF NOT EXISTS `example_app`;
GRANT ALL PRIVILEGES ON `example_app`.* TO 'example_app'@'%';
```