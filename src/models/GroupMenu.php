<?php

namespace toshcn\yii2\menu\models;

use Yii;

/**
 * This is the model class for table "{{%ant_group_menu}}".
 *
 * @property string $id
 * @property string $menu_id
 * @property string $group_id
 */
class GroupMenu extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%ant_group_menu}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['menu_id', 'group_id'], 'required'],
            [['menu_id', 'group_id'], 'integer'],
            [['menu_id', 'group_id'], 'unique', 'targetAttribute' => ['menu_id', 'group_id']],
            ['menu_id', 'exist', 'targetClass' => Menu::className(), 'targetAttribute' => 'id'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'menu_id' => 'Menu ID',
            'group_id' => 'Group ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMenu()
    {
        return $this->hasOne(Menu::className(), ['id' => 'menu_id']);
    }
}
