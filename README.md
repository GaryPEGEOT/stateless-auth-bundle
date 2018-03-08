# stateless-auth-bundle
![Build Status](https://travis-ci.org/GaryPEGEOT/stateless-auth-bundle.svg?branch=master)

Handle stateless authentication without SSH key needed. (Inspired from LexikJWTAuthenticationBundle)

## Getting started


### Prerequisites

This bundle requires Symfony 2.8+.

**Protip:** Though the bundle doesn't enforce you to do so, it is highly recommended to use HTTPS. 

Installation
------------

Add [`ghost-agency/stateless-auth-bundle`](https://packagist.org/packages/ghost-agency/stateless-auth-bundle)
to your `composer.json` file:

    php composer.phar require "ghost-agency/stateless-auth-bundle"

Register the bundle in `app/AppKernel.php`:

``` php
public function registerBundles()
{
    return array(
        // ...
        new GhostAgency\Bundle\StatelessAuthBundle\GhostAgencyStatelessAuthBundle(),
    );
}
```

### Configuration

Configure the hash key in your `config.yml` :

``` yaml
ghost_agency_stateless_auth:
    hash_key:  '%env(JWT_TOKEN_KEY)%'
    token_ttl: '%env(JWT_TOKEN_TTL)%' # Default to 3600 (1 hour)
```

Configure your `security.yml` :

``` yaml
security:
    # ...
    
    firewalls:

        main:
            pattern:  ^/api/login
            stateless: true
            anonymous: true
            json_login:
                check_path:               /api/login_check
                success_handler:          ghost_agency_stateless_auth.success_handler
                require_previous_session: false

        api:
            pattern:   ^/api
            stateless: true
            guard:
                authenticators:
                    - ghost_agency_stateless_auth.guard

    access_control:
        - { path: ^/api/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/api,       roles: IS_AUTHENTICATED_FULLY }
```

Configure your `routing.yml` :

``` yaml
api_login_check:
    path: /api/login_check
```

### Usage

#### 1. Obtain the token

The first step is to authenticate the user using its credentials.
A classical form_login on an anonymously accessible firewall will do perfect.

Just set the provided `ghost_agency_stateless_auth.success_handler` service as success handler to
generate the token and send it as part of a json response body.

Store it (client side), the JWT is reusable until its ttl has expired (3600 seconds by default).

Note: You can test getting the token with a simple curl command like this:

```bash
curl -X POST http://localhost:8000/api/login_check --data {"username": "Miaou", "password": "LeChat"}
```

If it works, you will receive something like this:

```json
{
   "token" : "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXUyJ9.eyJleHAiOjE0MzQ3Mjc1MzYsInVzZXJuYW1lIjoia29ybGVvbiIsImlhdCI6IjE0MzQ2NDExMzYifQ.nh0L_wuJy6ZKIQWh6OrW5hdLkviTs1_bau2GqYdDCB0Yqy_RplkFghsuqMpsFls8zKEErdX5TYCOR7muX0aQvQxGQ4mpBkvMDhJ4-pE4ct2obeMTr_s4X8nC00rBYPofrOONUOR4utbzvbd4d2xT_tj4TdR_0tsr91Y7VskCRFnoXAnNT-qQb7ci7HIBTbutb9zVStOFejrb4aLbr7Fl4byeIEYgp2Gd7gY"
}
```

### 2. Use the token

Simply pass the JWT on each request to the protected firewall as an authorization header.

By default only the authorization header mode is enabled : `Authorization: Bearer {token}`


#### Important note for Apache users

As stated in [this link](http://stackoverflow.com/questions/11990388/request-headers-bag-is-missing-authorization-header-in-symfony-2) and [this one](http://stackoverflow.com/questions/19443718/symfony-2-3-getrequest-headers-not-showing-authorization-bearer-token/19445020), Apache server will strip any `Authorization header` not in a valid HTTP BASIC AUTH format. 

If you intend to use the authorization header mode of this bundle (and you should), please add those rules to your VirtualHost configuration :

```apache
RewriteEngine On
RewriteCond %{HTTP:Authorization} ^(.*)
RewriteRule .* - [e=HTTP_AUTHORIZATION:%1]
```