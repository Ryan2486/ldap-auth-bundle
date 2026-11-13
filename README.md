# LDAP Auth Bundle

Symfony bundle providing configurable LDAP authentication with an overridable Twig login form.

The bundle ships everything needed to secure an application with an LDAP directory:

- A login/logout controller pair with configurable routes
- A form-login style authenticator (CSRF protected, session based)
- An LDAP credential checker built on `symfony/ldap`
- A user provider delegating user loading to a pluggable resolver
- A default styled login template, fully overridable
- A mock mode for local development and testing without a directory server

## Requirements

- PHP >= 8.2 with the `ldap` extension (Symfony 8.x requires PHP >= 8.4)
- Symfony 7.4 LTS or 8.x (`config`, `dependency-injection`, `http-foundation`, `http-kernel`, `routing`, `security-bundle`, `twig-bundle`, `yaml`)

## Installation

```bash
composer require ryan2486/ldap-auth-bundle
```

Enable the bundle in `config/bundles.php`:

```php
return [
    // ...
    LdapAuthBundle\LdapAuthBundle::class => ['all' => true],
];
```

Install the shipped assets (styles for the default login form):

```bash
php bin/console assets:install public
```

For development, use symlinks:

```bash
php bin/console assets:install public --symlink
```

## Configuration

Create `config/packages/ldap_auth.yaml`:

```yaml
ldap_auth:
    connection:
        dsn: '%env(LDAP_DSN)%'          # e.g. ldap://ldap.example.com:389
        encryption: none                 # none | tls | ssl
        protocol_version: 3
        referrals: false
        network_timeout: 5
    base_dn: '%env(LDAP_DN)%'             # suffix appended to the identifier to build the bind DN
    mock: '%env(bool:LDAP_MOCK)%'         # true bypasses the real directory (dev/test only)
    login_path: /login
    logout_path: /logout
    default_target_path: /                # redirect target after successful login
    user_resolver: App\Security\LdapUserResolver
```

Then define the variables in `.env`:

```dotenv
LDAP_DSN=ldap://ldap.example.com:389
LDAP_DN=@example.com
LDAP_MOCK=false
```

`base_dn` is concatenated with the submitted identifier to build the DN used for the LDAP
bind. For example, with `base_dn: '@example.com'`, user `jdoe` binds as `jdoe@example.com`.
If your directory requires a full DN instead (e.g. `uid=jdoe,ou=people,dc=example,dc=com`),
set `base_dn` accordingly, e.g. `',ou=people,dc=example,dc=com'` with identifiers of the
form `uid=jdoe`, or override the credential check by decorating
`LdapAuthBundle\Service\LdapProviderInterface`.

### Configuration reference

| Option                      | Type   | Default                    | Description                                             |
|-----------------------------|--------|----------------------------|---------------------------------------------------------|
| `connection.dsn`            | string | *required*                 | LDAP server URI                                          |
| `connection.encryption`     | string | `none`                     | One of `none`, `tls`, `ssl`                              |
| `connection.protocol_version` | int  | `3`                        | LDAP protocol version                                    |
| `connection.referrals`      | bool   | `false`                    | Follow LDAP referrals                                    |
| `connection.network_timeout` | int   | `5`                        | Network timeout in seconds                               |
| `base_dn`                   | string | *required*                 | Suffix appended to the identifier to build the bind DN   |
| `mock`                      | bool   | `false`                    | Bypass the directory and accept any credentials         |
| `login_path`                | string | `/login`                   | Login route path                                          |
| `logout_path`               | string | `/logout`                  | Logout route path                                         |
| `default_target_path`       | string | `/`                        | Post-login redirect target                               |
| `layout_template`           | string | `@LdapAuth/layout.html.twig`| Layout used by the login template                         |
| `login_template`            | string | `@LdapAuth/security/login.html.twig` | Login template                                  |
| `user_resolver`             | string | `LdapAuthBundle\Security\DefaultLdapUserResolver` | Service id of the user resolver         |

## Security wiring

Declare the bundle user provider and authenticator in `config/packages/security.yaml`:

```yaml
security:
    password_hashers:
        Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface: 'auto'

    providers:
        ldap_auth:
            id: LdapAuthBundle\Security\LdapUserProvider

    firewalls:
        main:
            lazy: true
            provider: ldap_auth
            entry_point: LdapAuthBundle\Security\LdapAuthenticator
            custom_authenticators:
                - LdapAuthBundle\Security\LdapAuthenticator
            logout:
                path: ldap_auth_logout
                target: ldap_auth_login

    access_control:
        - { path: ^/login, roles: PUBLIC_ACCESS }
```

## User resolution

After the LDAP bind succeeds, the bundle resolves the application user through
`LdapAuthBundle\Security\LdapUserResolverInterface`:

```php
namespace LdapAuthBundle\Security;

use Symfony\Component\Security\Core\User\UserInterface;

interface LdapUserResolverInterface
{
    public function resolve(string $identifier): UserInterface;
}
```

By default, `DefaultLdapUserResolver` returns an `LdapUser` carrying the identifier and
`ROLE_USER`. Most applications want to load users from their own storage and map
directory users to local roles instead.

Implement the interface and reference the service in the configuration:

```php
namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use LdapAuthBundle\Security\LdapUserResolverInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

final readonly class LdapUserResolver implements LdapUserResolverInterface
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    public function resolve(string $identifier): UserInterface
    {
        $user = $this->userRepository->find($identifier);

        if (null === $user) {
            throw new UserNotFoundException(sprintf('No user found for "%s".', $identifier));
        }

        return $user;
    }
}
```

```yaml
ldap_auth:
    user_resolver: App\Security\LdapUserResolver
```

Throwing `UserNotFoundException` results in a login failure. Throwing any other exception
is treated as a runtime error.

## How authentication works

1. `GET login_path` renders the login form (CSRF token id `ldap_auth`).
2. `POST login_path` is handled by `LdapAuthenticator`:
   - the CSRF token is validated;
   - the identifier and password must both be non-empty;
   - the credentials are checked against the directory: an LDAP bind is performed with
     `identifier . base_dn` as the bind DN. Invalid credentials fail authentication;
     connection failures surface as an authentication error instead of a crash;
   - on success, a `SelfValidatingPassport` is issued for the identifier.
3. The user provider resolves the identifier via the configured `user_resolver`.
4. On success, the user is redirected to the requested target path or `default_target_path`.
5. `logout_path` is handled by the Symfony logout listener.

## Development and testing

Enable the mock provider to work without a directory server:

```yaml
ldap_auth:
    mock: true
```

With the mock enabled, any non-empty identifier and password are accepted, except the
password `wrong`, which always fails. This is convenient for functional tests and local
development.

Run the bundle test suite:

```bash
composer install
vendor/bin/phpunit
```

## Templates

Default templates:

- `@LdapAuth/layout.html.twig`
- `@LdapAuth/security/login.html.twig`

Override them in the application:

- `templates/bundles/LdapAuthBundle/layout.html.twig`
- `templates/bundles/LdapAuthBundle/security/login.html.twig`

To extend the original bundle template, use the `@!` prefix:

```twig
{% extends '@!LdapAuth/layout.html.twig' %}
```

Alternatively, point the configuration to application templates:

```yaml
ldap_auth:
    layout_template: 'base.html.twig'
    login_template: 'security/login.html.twig'
```

## Override styles

The default CSS is scoped to `.ldap-auth` and exposes CSS custom properties:

```css
.ldap-auth {
    --ldap-auth-accent: #16a34a;
}
```

To load application CSS after the bundle CSS, override the `stylesheets` block:

```twig
{% extends '@!LdapAuth/layout.html.twig' %}

{% block stylesheets %}
    {{ parent() }}
    <link rel="stylesheet" href="{{ asset('styles/ldap-auth-overrides.css') }}">
{% endblock %}
```

To opt out of the bundle CSS entirely, override the block without `parent()` or use an
application layout via `layout_template`.
