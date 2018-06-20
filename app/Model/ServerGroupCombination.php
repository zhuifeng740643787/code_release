<?php
/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/6/14
 * Time: 上午9:25
 */
namespace App\Model;

class ServerGroupCombination extends BaseModel {

    protected $table = 'server_group_combination';

    public static function getServersByGroupId($group_id) {
        if (empty($group_id)) {
            return [];
        }
        return self::getInstance()->select('*', 'status=:STATUS and group_id=:GROUP_ID', [':STATUS' => static::ENABLE, ':GROUP_ID' => $group_id]);
    }

    public static function getServersByGroupIds($group_ids = []) {
        if (empty($group_ids)) {
            return [];
        }
        $in = str_repeat('?,', count($group_ids) - 1) . '?';
        $enable = static::ENABLE;
        return self::getInstance()->select('*', "status={$enable} and group_id in ($in)", $group_ids);
    }




}
