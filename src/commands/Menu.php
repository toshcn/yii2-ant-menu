<?php
/**
 * @link https://gitee.com/toshcn/yii2-ant-menu
 * @copyright Copyright (c) 2018 len168.com
 */

namespace toshcn\yii2\menu\commands;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;

class Menu extends Controller
{
    /**
     * if user's table not 'group_id' column, Please use this action create it or create it by your self.
     *
     * 给user表添加group_id字段
     */
    public function actionAddUserGroupIdColumn()
    {
        try {
            $migrate = new \yii\db\Migration();
            $migrate->addColumn(Yii::$app->menuManager->userTable, 'group_id', $migrate->integer()->unsigned()->defaultValue(0));
            echo 'Success added `group_id` to ' . Yii::$app->menuManager->userTable;
            return self::EXIT_CODE_ERROR;
        } catch (\Exception $e) {
            $this->stdout($e->getMessage(), Console::BG_RED);
            return self::EXIT_CODE_NORMAL;
        }
    }

    /**
     * Create an user group
     * @return int
     */
    public function actionAddGroup()
    {
        echo "Create an user group ...\n";
        $name = trim($this->prompt('Group name (max length 32 varchar):'));
        $desc = trim($this->prompt('Group description (max length 255 varchar):'));
        if ($name && $this->confirm("Are your sure to create an user group?")) {
            if (DIRECTORY_SEPARATOR === '\\') {
                $name = iconv('GBK','UTF-8//IGNORE', $name);
                $desc = iconv('GBK','UTF-8//IGNORE', $desc);
            }
            $model = Yii::$app->menuManager->addGroup($name, $desc);
            if ($model->hasErrors()) {
                echo join(',', $model->getFirstErrors()) . "\n";
                return self::EXIT_CODE_ERROR;
            }
            echo "Success.\n";
            return self::EXIT_CODE_NORMAL;
        }
        echo "Canceled!\n";
        return self::EXIT_CODE_NORMAL;
    }

    /**
     * Assign an menu to group
     * @return int
     */
    public function actionAssignMenu()
    {
        echo "Assign an menu to group ...\n";
        $menuId = intval($this->prompt('Menu id:'));
        $groupId = intval($this->prompt('Group id:'));
        if ($this->confirm("Are your sure assign an menu to group?")) {
            $model = Yii::$app->menuManager->assignMenu($menuId, $groupId);
            if ($model->hasErrors()) {
                echo join(',', $model->getFirstErrors()) . "\n";
                return self::EXIT_CODE_ERROR;
            }
            echo "Success.\n";
            return self::EXIT_CODE_NORMAL;
        }
        echo "Canceled!\n";
        return self::EXIT_CODE_NORMAL;
    }
}