# Troubleshooting Guide

## Common Issues

### Application Won't Start

**Problem**: "Connection refused" or blank page

**Solutions**:
1. Check if PHP is running:
```bash
   php artisan serve
```

2. Verify PHP version:
```bash
   php -v
```

3. Check .env file exists and has correct values

4. Clear cache:
```bash
   php artisan cache:clear
   php artisan config:clear
```

### Database Connection Error

**Problem**: "SQLSTATE[HY000] [1045] Access denied"

**Solutions**:
1. Verify MySQL is running:
```bash
   mysql -u root -p
```

2. Check .env credentials are correct

3. Verify database exists:
```bash
   mysql -u user -p -e "SHOW DATABASES;"
```

4. Test connection:
```bash
   php artisan tinker
   >>> DB::connection()->getPdo();
```

### 500 Internal Server Error

**Problem**: Blank white screen or "500" error

**Solutions**:
1. Enable debug mode temporarily:
```bash
   APP_DEBUG=true
```

2. Check logs:
```bash
   tail -f storage/logs/laravel.log
```

3. Check permissions:
```bash
   chmod -R 755 storage bootstrap/cache
```

4. Clear compiled cache:
```bash
   php artisan clear-compiled
   php artisan optimize
```

### Slow Performance

**Problem**: Application loads slowly or times out

**Solutions**:
1. Check database queries:
```bash
   php artisan tinker
   >>> DB::enableQueryLog();
   >>> // Run your operation
   >>> dump(DB::getQueryLog());
```

2. Enable caching:
```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
```

3. Check server resources:
```bash
   free -h
   df -h
   ps aux --sort=-%cpu | head -20
```

4. Optimize database:
```bash
   php artisan db:optimize
```

### Missing Tables Error

**Problem**: "Base table or view not found"

**Solutions**:
1. Run migrations:
```bash
   php artisan migrate
```

2. Check migration status:
```bash
   php artisan migrate:status
```

3. Rollback and retry:
```bash
   php artisan migrate:rollback
   php artisan migrate
```

### Permission Issues

**Problem**: "Permission denied" for storage or logs

**Solutions**:
```bash
# Fix ownership
sudo chown -R www-data:www-data .

# Fix permissions
chmod -R 755 storage bootstrap/cache
chmod -R 644 storage/logs/*
```

### Redis Connection Error

**Problem**: Redis cache not working

**Solutions**:
1. Check Redis is running:
```bash
   redis-cli ping
```

2. Verify .env settings:
```env
   CACHE_DRIVER=redis
   REDIS_HOST=127.0.0.1
   REDIS_PORT=6379
```

3. Fallback to file cache:
```env
   CACHE_DRIVER=file
```

### Email Not Sending

**Problem**: Emails not delivered

**Solutions**:
1. Check mail configuration in .env:
```env
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.mailtrap.io
   MAIL_PORT=465
```

2. Test with:
```bash
   php artisan tinker
   >>> Mail::raw('Test', function ($msg) { $msg->to('test@example.com'); });
```

3. Check logs:
```bash
   tail -f storage/logs/laravel.log
```

## Debug Mode

### Enable Debug Mode

Edit `.env`:
```env
APP_DEBUG=true
```

**Warning**: Never enable in production!

### Using Tinker REPL

```bash
php artisan tinker
```

Examples:
```php
# Query data
>>> App\Models\Sale::count()
>>> App\Models\Customer::find(1)

# Test methods
>>> auth()->user()
>>> Hash::make('password')

# Artisan commands
>>> Artisan::call('migrate')
```

## Log Files

Location: `storage/logs/laravel.log`

Monitor in real-time:
```bash
tail -f storage/logs/laravel.log
```

Search logs:
```bash
grep "error\|exception" storage/logs/laravel.log
```

## Performance Monitoring

### Query Performance

```bash
php artisan tinker
>>> DB::enableQueryLog();
>>> // Run operation
>>> collect(DB::getQueryLog())->map(function($q) { return $q['time']; })->sum()
```

### Memory Usage

```bash
# Peak memory
php -r "echo memory_get_peak_usage(true);"

# During operation
php artisan tinker
>>> memory_get_usage()
```

### CPU Usage

```bash
top -b -n 1 | grep php
ps aux | grep php
```

## Database Optimization

### Analyze Tables

```sql
ANALYZE TABLE sales;
ANALYZE TABLE customers;
ANALYZE TABLE products;
```

### Optimize Tables

```sql
OPTIMIZE TABLE sales;
```

### Check Table Status

```sql
SHOW TABLE STATUS;
```

### Add Missing Indexes

```sql
ALTER TABLE sales ADD INDEX idx_customer_id (customer_id);
ALTER TABLE sales ADD INDEX idx_sale_date (sale_date);
```

## Backup & Recovery

### Create Database Backup

```bash
mysqldump -u user -p database > backup.sql
```

### Restore Backup

```bash
mysql -u user -p database < backup.sql
```

### Verify Backup

```bash
# Check file size
ls -lh backup.sql

# Count tables
grep "^CREATE TABLE" backup.sql | wc -l
```

## Getting Help

1. **Check Logs**: Always check `storage/logs/laravel.log` first
2. **Search Docs**: Look through documentation
3. **Try Clearing Cache**: `php artisan cache:clear`
4. **Restart Services**: `sudo systemctl restart php8.2-fpm`
5. **Contact Support**: support@yourdomain.com

---

**Last Updated**: November 2024