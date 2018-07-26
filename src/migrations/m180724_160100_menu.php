<?php

use yii\db\Migration;

/**
 * Class m180724_160100_config
 */
class m180724_160100_menu extends Migration
{
    const TBL_MENU = '{{%ant_menu}}';
    const TBL_GROUP_MENU = '{{%ant_group_menu}}';
    const TBL_GROUP = '{{%ant_group}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(static::TBL_MENU, [
            'id' => $this->primaryKey()->unsigned()->unique()->notNull(),
            'parent_id' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'menu_name' => $this->string(20)->notNull(),
            'menu_icon' => $this->string(32)->notNull()->defaultValue(''),
            'menu_path' => $this->string(128)->notNull()->defaultValue(''),
            'hide_in_menu' => $this->boolean()->notNull()->defaultValue(0),
            'hide_in_breadcrumb' => $this->boolean()->notNull()->defaultValue(0),
            'menu_sort' => $this->integer()->unsigned()->notNull()->defaultValue(999),
            'create_at' => $this->dateTime()->notNull(),
            'update_at' => $this->dateTime()->Null(),
        ], $tableOptions);

        // insert menu data
        $now = date('Y-m-d H:i:s');
        $this->batchInsert(static::TBL_MENU, ['id', 'parent_id', 'menu_name', 'menu_icon', 'menu_path', 'hide_in_menu', 'hide_in_breadcrumb', 'menu_sort', 'create_at'], [
            [1, 0, '仪表盘', 'dashboard', 'dashboard', 0, 0, 999, $now],
            [2, 1, '分析页', '', 'analysis', 0, 0, 999, $now],
            [3, 1, '监控页', '', 'monitor', 0, 0, 999, $now],
            [4, 1, '工作台', '', 'workplace', 0, 0, 999, $now],
            [5, 0, '系统', '', 'system', 0, 0, 999, $now],
            [6, 5, '菜单管理', '', 'menu', 0, 0, 999, $now],
        ]);

        $this->createTable(static::TBL_GROUP, [
            'id' => $this->primaryKey()->unsigned()->unique()->notNull(),
            'group_name' => $this->string(32)->unique()->notNull(),
            'group_description' => $this->string(255)->notNull()->defaultValue(''),
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'create_at' => $this->dateTime()->notNull(),
            'update_at' => $this->dateTime()->Null(),
        ], $tableOptions);
        $this->batchInsert(static::TBL_GROUP, ['id', 'group_name', 'group_description', 'create_at'], [
            [1, 'admin', '超级管理员', $now],
        ]);

        $this->createTable(static::TBL_GROUP_MENU, [
            'id' => $this->primaryKey()->unsigned()->unique()->notNull(),
            'menu_id' => $this->integer()->unsigned()->notNull(),
            'group_id' => $this->integer()->unsigned()->notNull(),
        ], $tableOptions);
        $this->createIndex('idx-menu_id-group_id', static::TBL_GROUP_MENU, ['menu_id', 'group_id'], true);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        //echo "m180723_064449_config cannot be reverted.\n";
        $this->dropTable(static::TBL_MENU);
        $this->dropTable(static::TBL_GROUP_MENU);
        $this->dropTable(static::TBL_GROUP);
    }
}
