<?php

namespace toshcn\yii2\menu\models;

use Yii;

/**
 * This is the model class for table "{{%menu}}".
 *
 * @property string $id
 * @property string $parent_id
 * @property string $menu_name
 * @property string $menu_icon
 * @property string $menu_path
 * @property int $hide_in_menu
 * @property int $hide_in_breadcrumb
 * @property string $menu_sort
 * @property string $create_at
 * @property string $update_at
 */
class Menu extends \yii\db\ActiveRecord
{
    const HIDE_IN_MENU_YES = 1;
    const HIDE_IN_MENU_NO = 0;
    const HIDE_IN_BREADCRUMB_YES = 1;
    const HIDE_IN_BREADCRUMB_NO = 0;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%ant_menu}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['parent_id', 'menu_sort'], 'integer'],
            [['menu_name', 'create_at'], 'required'],
            [['menu_name', 'menu_icon', 'menu_path'], 'trim'],
            [['hide_in_menu', 'hide_in_breadcrumb'], 'default', 'value' => 0],
            [['create_at', 'update_at', 'hide_in_menu', 'hide_in_breadcrumb'], 'safe'],
            [['menu_name'], 'string', 'max' => 20],
            [['menu_icon'], 'string', 'max' => 32],
            [['menu_path'], 'string', 'max' => 128],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'parent_id' => 'Parent ID',
            'menu_name' => 'Menu Name',
            'menu_icon' => 'Menu Icon',
            'menu_path' => 'Menu Path',
            'hide_in_menu' => 'Hide In Menu',
            'hide_in_breadcrumb' => 'Hide In Breadcrumb',
            'menu_sort' => 'Menu Sort',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }
}
