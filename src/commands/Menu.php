<?php
/**
 * @link https://gitee.com/toshcn/yii2-ant-menu
 * @copyright Copyright (c) 2018 len168.com
 */

namespace toshcn\yii2\menu\commands;

use toshcn\yii2\menu\MenuManager;
use yii\console\Controller;
use yii\helpers\Console;

class Menu extends Controller
{
    public function options($actionID)
    {
        return ['add-group-id'];
    }

    /**
     * if user's table not 'group_id' column, Please use this action create it or create it by your self.
     *
     * 给user表添加group_id字段
     */
    public function actionIndex()
    {
        try {
            $manager = new MenuManager();
            $migrate = new \yii\db\Migration();
            $migrate->addColumn($manager->userTable, 'group_id', $migrate->integer()->unsigned()->defaultValue(0));
            echo 'added `group_id` to ' . $manager->userTable;
            return 1;
        } catch (\Exception $e) {
            $this->stdout($e->getMessage(), Console::BG_RED);
            return 0;
        }
    }
}