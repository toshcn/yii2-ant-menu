<?php
/**
 * @link https://gitee.com/toshcn/yii2-ant-menu
 * @copyright Copyright (c) 2018 len168.com
 */

namespace toshcn\yii2\menu;

use toshcn\yii2\menu\models\GroupMenu;
use toshcn\yii2\menu\models\Menu;
use yii\caching\Cache;
use yii\di\Instance;


class MenuManager extends \yii\base\Object
{
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
     * 通过菜单ID 获取菜单
     * @param $id 菜单id
     * @return null|static
     */
    public function findMenuById($id)
    {
        return Menu::findOne($id);
    }

    /**
     * 获取组全部菜单
     * @param $groupId
     * @return mixed
     */
    public function findMenusByGroupId($groupId)
    {
        return GroupMenu::find()->where(['group_id' => $groupId])->joinWith('menu');
    }

    /**
     * @param $groupId
     */
    public function transformAntMenu($groupId)
    {
        $data = $this->findMenusByGroupId($groupId)->orderBy(['menu_sort' => SORT_ASC])->asArray()->all();

        return $data;
    }

    /**
     * 查找子节点
     * @param $data 菜单数组
     * @param int $pid 父级id
     * @return array
     */
    public static function findChild(&$data, $pid = 0) {
        $rootList = array();
        foreach ($data as $key => $val) {
            if ($val['menu']['parent_id'] == $pid) {
                $rootList[]   = $val;
                unset($data[$key]);
            }
        }
        return $rootList;
    }

    public static function transformTreeMenu(&$data, $parent = 0)
    {
        $children = static::findChild($menus, $parent);
        if (empty($children)) {
            return [];
        }
        foreach ($children as $key => $item) {
            $children[$key]['children'] = [];
            $list = static::formatMenu( $menus, $item->id);
            if ($list) {
                $children[$key]['children'] = $list;
            }
        }
        return $children;
    }

    /**
     * 给组分配菜单
     * @param $menuId 菜单Id
     * @param $groupId 组Id
     * @return GroupMenu
     */
    public function assignMenu($menuId, $groupId)
    {
        $model = new GroupMenu();
        $model->menu_id = $menuId;
        $model->group_id = $groupId;
        $model->save();

        return $model;
    }

    /**
     * 删除单个已分配的菜单
     * @param $menuId 菜单ID
     * @param $groupId 组ID
     * @return mixed
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function removeAssignment($menuId, $groupId)
    {
        return GroupMenu::findOne(['menu_id' => $menuId, 'group_id' => $groupId])->delete();
    }

    /**
     * 删除全部已分配的菜单
     * @param $groupId 组ID
     * @return mixed
     */
    public function removeAllAssignment($groupId)
    {
        return GroupMenu::find()->where(['group_id' => $groupId])->delete();
    }

    /**
     * 添加菜单
     * @param $parent 父菜单ID
     * @param $name 菜单名称
     * @param $path 菜单ant路径
     * @param string $icon 图标
     * @param int $sort 排序 0-n
     * @param boolean $hideInMenu 是否在菜单中隐藏：true是，false否
     * @param boolean $hideInBreadcrumb 是否在面包屑菜单中隐藏：true是，false否
     * @return Menu
     */
    public function addMenu($parent, $name, $path, $icon = '', $sort = 999, $hideInMenu = false, $hideInBreadcrumb = false)
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