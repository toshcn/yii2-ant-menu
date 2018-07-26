<?php
/**
 * @link https://gitee.com/toshcn/yii2-ant-menu
 * @copyright Copyright (c) 2018 len168.com
 */

namespace toshcn\yii2\menu;

use toshcn\yii2\menu\models\Group;
use toshcn\yii2\menu\models\GroupMenu;
use toshcn\yii2\menu\models\Menu;
use yii\caching\Cache;
use yii\di\Instance;

/**
 * for Ant design menu manager
 * usage: config your components
 * ```php
 *  'components' => [
 *      'menuManager' => [
 *          'class' => 'toshcn\yii2\menu\MenuManager',
 *          'cache' = 'cache',
 *          'cacheKey' => 'menu_cache_key',
 *          'expire' => 3600,
 *          'userTable' => '{{%user}}',
 *          'superAdminGroupId' => 1
 *      ],
 *   ]
 * ```
 *
 * @package toshcn\yii2\menu
 */
class MenuManager extends \yii\base\Object
{
    public $version = '1.0.0';
    
    /**
     * @var string user's table
     */
    public $userTable = '{{%user}}';

    /**
     * @var string The cache component 缓存组件
     */
    public $cache = 'cache';

    /**
     * @var string cache key 缓存key
     */
    public $cacheKey = 'toshcn_ant_menu_';

    /**
     * @var int cache expire time 缓存时间
     */
    public $expire = 36000;

    /**
     * @var int the super administrator group id
     */
    public $superAdminGroupId = 1;

    /**
     * MenuManager constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();
        $this->cache = Instance::ensure($this->cache, Cache::className());
    }

    /**
     * 获取菜单查询对象
     * @return \yii\db\ActiveQuery
     */
    public function findMenu()
    {
        return Menu::find();
    }

    /**
     * 获取用户组查询对象
     * @return \yii\db\ActiveQuery
     */
    public function findGroup()
    {
        return Group::find();
    }

    /**
     * 通过菜单ID 获取菜单
     * @param $id 菜单id
     * @return null|static
     */
    public function findMenuById($id)
    {
        return Menu::findOne($id);
    }

    /**
     * 通过用户组ID 获取用户组
     * @param $id 用户组id
     * @return null|static
     */
    public function findGroupById($id)
    {
        return Group::findOne($id);
    }

    /**
     * 查找会员组菜单, 如果属于超级管理员组返回全部菜单
     * @param $groupId 会员组ID
     * @return mixed
     */
    public function findMenusByGroupId($groupId)
    {
        if ($groupId === $this->superAdminGroupId) {
            return Menu::find();
        }
        return Group::findOne($groupId)->getMenus();
    }

    /**
     * 获取会员组菜单，整理成ant design 所需的菜单结构
     * @param $groupId 会员组ID
     * @return array
     */
    public function getAntMenu($groupId)
    {
        $data = $this->cache->get($this->cacheKey . $groupId);
        if ($data === false) {
            $rows = $this->findMenusByGroupId($groupId)->orderBy(['menu_sort' => SORT_ASC])->asArray()->all();
            $data = static::transformTreeMenu($rows);
            unset($rows);
            $this->cache->set($this->cacheKey . $groupId, $data, $this->expire);
        }

        return $data;
    }

    /**
     * 查找子节点，整理成ant design 所需的菜单结构
     * @param $data 菜单数组
     * @param int $pid 父级id
     * @return array
     */
    public static function findChild(&$data, $pid = 0) {
        $rootList = array();
        foreach ($data as $key => $val) {
            if ($val['parent_id'] == $pid) {
                $temp['id']     = $val['id'];
                $temp['name']   = $val['menu_name'];
                if ($val['menu_icon']) {
                    $temp['icon'] = $val['menu_icon'];
                }

                $temp['path']             = $val['menu_path'];
                $temp['hideInMenu']       = $val['hide_in_menu'] ? true : false;
                $temp['hideInBreadcrumb'] = $val['hide_in_breadcrumb'] ? true : false;
                $rootList[] = $temp;
                unset($data[$key]);
            }
        }
        return $rootList;
    }

    /**
     * 整理菜单数据成层次数组
     * @param &$data 菜单数组
     * @param int $parent 父菜单id
     * @return array
     */
    public static function transformTreeMenu(&$data, $parent = 0)
    {
        $menus = static::findChild($data, $parent);
        if (empty($menus)) {
            return [];
        }
        foreach ($menus as $key => $item) {
            $list = static::transformTreeMenu($data, $item['id']);
            unset($menus[$key]['id']);
            if ($list) {
                $menus[$key]['children'] = $list;
            }
        }
        return $menus;
    }

    /**
     * 给会员组分配菜单
     * @param $menuId 菜单Id
     * @param $groupId 会员组ID
     * @return GroupMenu
     */
    public function assignMenu($menuId, $groupId)
    {
        $model = new GroupMenu();
        $model->menu_id = $menuId;
        $model->group_id = $groupId;
        if ($model->save()) {
            $this->cleanCache();
        }

        return $model;
    }

    /**
     * 删除单个已分配的菜单
     * @param $menuId 菜单ID
     * @param $groupId 会员组ID
     * @return boolean
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function removeAssignment($menuId, $groupId)
    {
        if (GroupMenu::findOne(['menu_id' => $menuId, 'group_id' => $groupId])->delete()) {
            $this->cleanCache();
            return true;
        }

        return false;
    }

    /**
     * 删除全部已分配的菜单
     * @param $groupId 会员组ID
     * @return boolean
     */
    public function removeAllAssignment($groupId)
    {
        if (GroupMenu::find()->where(['group_id' => $groupId])->delete()) {
            $this->cleanCache();
            return true;
        }

        return false;
    }

    /**
     * 添加菜单
     * @param $name 菜单名称
     * @param $path 菜单ant路径
     * @param integer $parent 父菜单ID
     * @param string $icon 图标
     * @param int $sort 排序 0-n
     * @param boolean $hideInMenu 是否在菜单中隐藏：true是，false否
     * @param boolean $hideInBreadcrumb 是否在面包屑菜单中隐藏：true是，false否
     * @return Menu
     */
    public function addMenu($name, $path, $parent = 0, $icon = '', $sort = 999, $hideInMenu = false, $hideInBreadcrumb = false)
    {
        $model = new Menu();
        $model->parent_id = $parent;
        $model->menu_name = $name;
        $model->menu_path = $path;
        $model->menu_icon = $icon;
        $model->menu_sort = $sort;
        $model->hide_in_menu = $hideInMenu === true ? Menu::HIDE_IN_MENU_YES : Menu::HIDE_IN_MENU_NO;
        $model->hide_in_breadcrumb = $hideInBreadcrumb === true ? Menu::HIDE_IN_BREADCRUMB_YES : Menu::HIDE_IN_BREADCRUMB_NO;
        $model->create_at = date('Y-m-d H:i:s');
        if ($model->save()) {
            $this->cleanCache();
        }

        return $model;
    }

    /**
     * 更新菜单
     * @param $id 菜单ID
     * @param string $name 菜单名称
     * @param string $path 菜单ant路径
     * @param string $icon 图标
     * @param int $parent 父菜单ID
     * @param int $sort 排序 0-n
     * @param boolean $hideInMenu 是否在菜单中隐藏：true是，false否
     * @param boolean $hideInBreadcrumb 是否在面包屑菜单中隐藏：true是，false否
     * @return Menu
     */
    public function updateMenuById($id, $name = '', $path = '', $icon = '', $parent = 0, $sort = 0, $hideInMenu = false, $hideInBreadcrumb = false)
    {
        if ($model = Menu::findOne($id)) {
            if ($name) {
                $model->menu_name = $name;
            }
            if ($path) {
                $model->menu_path = $path;
            }
            if ($icon) {
                $model->menu_icon = $icon;
            }
            if ($parent) {
                $model->parent_id = $parent;
            }
            if ($sort) {
                $model->menu_sort = $sort;
            }
            if ($model->hide_in_menu != $hideInMenu) {
                $model->hide_in_menu       = $hideInMenu === true ? Menu::HIDE_IN_MENU_YES : Menu::HIDE_IN_MENU_NO;
            }
            if ($model->hide_in_breadcrumb != $hideInBreadcrumb) {
                $model->hide_in_breadcrumb = $hideInBreadcrumb === true ? Menu::HIDE_IN_BREADCRUMB_YES : Menu::HIDE_IN_BREADCRUMB_NO;
            }
            $model->update_at = date('Y-m-d H:i:s');
            if ($model->save()) {
                $this->cleanCache();
            }
        }
        return $model;
    }

    /**
     * 添加用户组
     * @param $name 用户组名称
     * @param string $description 用户组描述
     * @return Group
     */
    public function addGroup($name, $description = '')
    {
        $model = new Group();
        $model->group_name = $name;
        $model->group_description = $description;
        $model->create_at = date('Y-m-d H:i:s');
        $model->save();

        return $model;
    }

    /**
     * 更新用户组
     * @param $id 用户组ID
     * @param $name 用户组名称
     * @param string $description 用户组描述
     * @return Group
     */
    public function updateGroupById($id, $name, $description = '')
    {
        if ($model = Group::findOne($id)) {
            $model->group_name        = $name;
            $model->group_description = $description;
            $model->save();
        }

        return $model;
    }

    /**
     * 清空缓存
     * @param int $groupId
     */
    protected function cleanCache($groupId = 0)
    {
        if ($groupId) {
            $this->cache->delete($this->cacheKey . $groupId);
        } else {
            $groups = GroupMenu::find()->groupBy('group_id')->all();
            foreach ($groups as $key => $item) {
                $this->cache->delete($this->cacheKey . $item->group_id);
            }
            unset($groups);
        }
    }
}