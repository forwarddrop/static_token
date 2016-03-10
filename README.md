# Static token

The modules provides a way to automatically secure GET URLs, much like csrf_token does, but without the requirement to have sessions.
This is handy for example when you integrate with a 3rd party API, and you want it to call something on your site.

## Usage

Add something like this into your routing definitions:

```
example.route:
  path: '/example/{id}/{id2}'
  requirements:
    _static_token: '{id}/{id2}'
```

Done

Ensure to not forget to add static_token as requirement of your module.

# Installation

Like any other module.
