# User Panel

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
		# username => role[] or username => password
		admin: [role1, role2]
		user: [role2, role3]
		user2: password
```
