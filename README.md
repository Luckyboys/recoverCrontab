# Recover Crontab

We use the crontab log to recover crontab file

need php 5.6

```php
php recoveryCrontab.php logFilePath startTime endTime
Example: 
php recoveryCrontab.php /var/log/cron 2016-12-12 2016-12-13
```
