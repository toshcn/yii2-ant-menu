<?php

namespace toshcn\yii2\menu\models;

use Yii;

/**
 * This is the model class for table "{{%ant_group}}".
 *
 * @property string $id
 * @property string $group_name
 * @property string $group_description
 * @property int $status
 * @property string $create_at
 * @property string $update_at
 */
class Group extends \yii\db\ActiveRecord
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%ant_group}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['group_name', 'create_at'], 'required'],
            [['status'], 'integer'],
            [['status'], 'default', 'value' => static::STATUS_ACTIVE],
            [['create_at', 'update_at'], 'safe'],
            [['group_name'], 'string', 'max' => 32],
            [['group_name'], 'unique'],
            [['group_description'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'group_name' => 'Group Name',
            'group_description' => 'Group Description',
            'status' => 'Status',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroupMenus()
    {
        return $this->hasMany(GroupMenu::className(), ['group_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMenus()
    {
        return $this->hasMany(Menu::className(), ['id' => 'menu_id'])->via('groupMenus');
    }
}
