# User Panel

**Waiting for merge PR [1320](https://github.com/nette/nette/pull/1320).**

Simple user panel for [Nette Framework](http://nette.org/)

## Instalation

The best way to install jedenweb/user-panel is using  [Composer](http://getcomposer.org/):


```json
{
	"require": {
		"jedenweb/user-panel": "dev-master"
	}
}
```

After that you have to register extension in config.neon.

```neon
extensions:
	userPanel: JedenWeb\UserPanel\DI\UserPanelExtension
```

## Usage

Add users to config.neon:

```neon
userPanel:
	column: email # column email will be used as username
	credentials:
		# username => role[]
		admin: [role1, role2]
		"Diacritics ěščř": [role1, role3]
		user: [role2, role3]
```

Note: Option ```column``` is currently unused, see [Todo](#todo).

## Todo

- Allow authentication by *username* and *password* instead of creating dummy ```Identity```
