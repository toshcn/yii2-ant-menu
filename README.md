ant menu component
==================
一个为ant design 设计的菜单组件

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist toshcn/ant-menu "*"
```

or add

```
"toshcn/ant-menu": "*"
```

to the require section of your `composer.json` file.


Usage
-----

配置应用组件:如common->config->main.php

```php
'components' => [
    'menuManager' => [
        'class' => 'toshcn\yii2\menu\MenuManager',
        'cache' = 'cache',
        'cacheKey' => 'menu_cache_key',
        'expire' => 3600,
        'userTable' => '{{%user}}',
        'superAdminGroupId' => 1
    ],
]
```

配置命令行: 基础模板在config->console.php，高级模板在console->config->main.php
```php
'controllerMap' => [
    'menu' => [
        'class' => 'toshcn\yii2\menu\commands\Menu',
    ],
],
```

运行数据迁移：
```
php yii migrate --migrationPath=@vendor/toshcn/yii2/menu/migrations
```

为用户表添加`group_id`字段
```
php yii menu/add-user-group-id-column
```

添加一个用户组：
```
Yii::$app->menuManager->addGroup('user', '用户组说明：普通用户组');
```

添加一个菜单：
```
$parentId = 0;
Yii::$app->menuManager->addMenu('menu_name', 'path', $parentId, 'icon');
```

给用户组分配一个菜单：
```
$menuId = 1;
$groupId = 1;
Yii::$app->menuManger->assignMenu($menuId, $groupId);
```