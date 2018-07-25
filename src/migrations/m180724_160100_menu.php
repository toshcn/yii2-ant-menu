<?php

use yii\db\Migration;

/**
 * Class m180724_160100_config
 */
class m180724_160100_menu extends Migration
{
    const TBL_MENU = '{{%ant_menu}}';
    const TBL_GROUP_MENU = '{{%ant_group_menu}}';

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
    }
}
