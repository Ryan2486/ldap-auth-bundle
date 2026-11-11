# LDAP Auth Bundle

Symfony bundle providing configurable LDAP authentication with an overridable Twig login form.

## Install assets

The bundle ships default styles in `public/css/ldap-auth.css`. Install them into the application's public directory:

```bash
php bin/console assets:install public
```

For development, use symlinks:

```bash
php bin/console assets:install public --symlink
```

The default layout references:

```twig
<link rel="stylesheet" href="{{ asset('bundles/ldapauth/css/ldap-auth.css') }}">
```

When AssetMapper is enabled, this resolves to a versioned `/assets/bundles/ldapauth/css/ldap-auth-*.css` URL.

## Templates

Default templates:

- `@LdapAuth/layout.html.twig`
- `@LdapAuth/security/login.html.twig`

## Override templates

Create override files in the application:

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

To opt out of the bundle CSS entirely, override the block without `parent()` or use an application layout via `layout_template`.
