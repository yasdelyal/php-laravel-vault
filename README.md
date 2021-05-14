# PHP Laravel Vault [![Documentation Status](https://readthedocs.org/projects/php-laravel-vault/badge/?version=latest)](https://php-laravel-vault.readthedocs.io/en/latest/?badge=latest)

Get your .env from remote (HaspiCorp Vault) on deploy

> **Warning! This is very beginning alpha version without usable realise.**
> **Not recommended for using now**

## Quickstart
### Install
```shell
composer require yasdelyal/php-laravel-vault
php artisan vendor:publish --tag=config --provider="YaSdelyal\LaravelVault\LaravelVaultServiceProvider"

Copied File [/vendor/yasdelyal/php-laravel-vault/config/vault.php] To [/config/vault.php]
```
### Configure
Add patches from Vault and variables to secrets in vault.php
```php 
'vars' => [
    'patches' => [
        '/secret/database/{env}'
    ],
    'patch_variables' => [
        'env' => 'production',
    ],
  ]
```

### Override credentials
Make vault.json file with Vault options - structure MUST be same as vault.php

You can override here ALL options from vault.php
```json
{
  "connections": {
    "vault": {
      "host": "http://vault",
      "role_id": "your_secret_id",
      "secret_id": "your_secret_id"
    }
  }
}
```
### Use
```shell
base64 -w 0 vault.json | php artisan vault:get --stdin --b64
```

If all OK (credentials is actual and have access to secret patches), you see merged values from all patches:
```shell
+---------+------------+
| Key     | Value      |
+---------+------------+
| secret1 | value1     |
| secret2 | value2     |
+---------+------------+
```
- For save this in .env - add option --output=currentEnv
- For save this in .env.next - add option --output=nextEnv

### Use in CI

Here is a shorthand command special from CI
- On runner, obtain a token [docs](https://learn.hashicorp.com/tutorials/vault/pattern-approle?in=vault/recommended-patterns)
- Obtain .env with that token
```shell
php artisan vault:ci s.JYVfe67632rRDtyf --app=my_project --env=production
```
- s.JYVfe67632rRDtyf - Vault one-time token
- my_project - App name, set the 'app' patch variable. Optional.
- production - App env, set the 'env' patch variable. Optional.


## Documentation
Documentation WILL BE available here: https://php-laravel-vault.readthedocs.io/

