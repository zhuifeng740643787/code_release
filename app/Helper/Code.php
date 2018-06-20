<?php
/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/6/15
 * Time: 上午9:55
 */

namespace App\Helper;

use Kafka\Exception;

class Code
{

    /**
     * 拉取最新代码
     * @param $code_path
     * @param $repository
     * @return bool|string
     */
    private static function _pullCode($code_path, $repository)
    {
        $deploy_config = app()->config->get('deploy');
        $git = $deploy_config['local_git_bin'];
        if (!file_exists($code_path)) {
            if (false === Utils::runExec("mkdir -p $code_path ")) {
                return '执行有误，无法创建文件';
            }
        }
        // 判断是否有代码, 没有则拉取master分支的代码
        if (!file_exists($code_path . DS . '.git')) {
            $ret = Utils::runExec("cd $code_path && rm -rf ./* && rm -rf ./.git && $git clone $repository $code_path");
            if (false === $ret) {
                return '代码拉取失败';
            }
        }
        return true;
    }

    // 获取仓库的所有分支
    public static function getRepositoryBranches($project_name, $repository)
    {
        $deploy_config = app()->config->get('deploy');
        $git = $deploy_config['local_git_bin'];
        $code_path = realpath($deploy_config['local_tmp_code_path']) . DS . $project_name;

        // 拉取最新代码
        $pull_code = self::_pullCode($code_path, $repository);
        // 看是否报错
        if (is_string($pull_code)) {
            return $pull_code;
        }

        $ret = Utils::runExec("cd $code_path && $git branch -a");
        if (false === $ret) {
            return '获取分支失败';
        }

        $branch_arr = [];
        foreach ($ret as $row) {
            if (strpos($row, 'remotes/origin') === false) {
                continue;
            }
            if (strpos($row, 'remotes/origin/HEAD') !== false) {
                continue;
            }
            $branch_arr[] = trim(str_replace('remotes/origin/', '', $row));
        }
        return $branch_arr;
    }

    // 获取仓库的所有TAG
    public static function getRepositoryTags($project_name, $repository)
    {
        $deploy_config = app()->config->get('deploy');
        $git = $deploy_config['local_git_bin'];
        $code_path = realpath($deploy_config['local_tmp_code_path']) . DS . $project_name;

        // 拉取最新代码
        $pull_code = self::_pullCode($code_path, $repository);
        // 看是否报错
        if (is_string($pull_code)) {
            return $pull_code;
        }

        $ret = Utils::runExec("cd $code_path && $git tag");
        if (false === $ret) {
            return '获取tag失败';
        }

        return $ret;
    }

    /**
     * 验证服务器是否可用
     * @param $name 服务器名称
     * @param $host 服务器IP
     * @param $user 用户名
     * @return bool
     */
    public static function isServerVaild($name, $host, $user)
    {
        $deploy_path = PROJECT_ROOT . DS . 'deploy' . DS . 'valid_server';
        $deploy_config = app()->config->get('deploy');
        $config = [
            'remote_servers' => [
                [
                    'name' => $name,
                    'host' => $host,
                    'user' => $user,
                ]
            ],
            'identity_file_path' => $deploy_config['identity_file_path']
        ];

        if (!self::writeConfigFile($deploy_path . DS . 'config.php', $config)) {
            Log::error('写入配置文件失败:' . $deploy_path);
            return false;
        }

        $ret = self::runDep($deploy_path, 'valid_server', $name);
        if (false === $ret) {
            return false;
        }
        if (isset($ret[1]) && $ret[1] == 1) {
            return true;
        }
        return false;
    }


    /**
     * 获取仓库提交的head信息
     * @param $project_name 项目名称
     * @return bool
     * @throws \Exception
     */
    public static function getGitHeadCommit($project_name)
    {
        $deploy_config = app()->config->get('deploy');
        $git = $deploy_config['local_git_bin'];
        $code_path = realpath($deploy_config['local_tmp_code_path']) . DS . $project_name;
        if (!file_exists($code_path)) {
            throw new \Exception('项目未找到:' . $code_path);
        }
        return Utils::runExec("cd $code_path && $git log |head -n 1");
    }

    /**
     * 复制代码到指定目录
     * @param $project_name 项目名称
     * @param $destination_code_path 目标代码路径
     * @return bool
     * @throws \Exception
     */
    public static function copyProjectCode($project_name, $destination_code_path)
    {
        $deploy_config = app()->config->get('deploy');
        $source_code_path = $deploy_config['local_tmp_code_path'];
        $code_path = $source_code_path . DS . $project_name;
        if (!file_exists($code_path)) {
            throw new \Exception('项目未找到:' . $code_path);
        }
        if (!file_exists($destination_code_path) && false === Utils::runExec("mkdir -p $destination_code_path")) {
            throw new \Exception("无法创建文件：$destination_code_path");
        }
        // 复制文件
        return Utils::runExec("cd $source_code_path && cp -R $code_path $destination_code_path");
    }

    // 获取分支最新代码
    public static function getBranchCode($code_path, $branch)
    {
        $deploy_config = app()->config->get('deploy');
        $git = $deploy_config['local_git_bin'];
        if (!file_exists($code_path)) {
            throw new \Exception('项目未找到:' . $code_path);
        }

        // 判断是否有代码, 没有则拉取master分支的代码
        if (!file_exists($code_path . DS . '.git')) {
            throw new \Exception('项目无代码:' . $code_path);
        }

        // 需要拉取的分支
        $branch = str_replace('remotes/origin/', '', $branch);
        // 判断本地是否已含有所要拉取的分支
        $local_branches = trim(Utils::runExec("cd $code_path && $git branch --column"));
        $local_branch_arr = explode(' ', $local_branches);

        // 当前分支名称
        $current_branch = $local_branch_arr[array_search('*', $local_branch_arr) + 1];

        // 是当前分支，拉取最新代码
        if ($current_branch == $branch) {
            return Utils::runExec("cd $code_path && $git pull");
        }

        // clean当前分支的代码,以便切换分支
        if (!self::isBranchClean($code_path, $git)) {
            Utils::runExec("cd $code_path && $git add -A && $git stash");
        }
        // 判断是否存在于本地分支
        if (!in_array($branch, $local_branch_arr)) {
            return Utils::runExec("cd $code_path && $git checkout remotes/origin/$branch -b $branch");
        }
        return Utils::runExec("cd $code_path && $git checkout $branch && $git pull");
    }

    /**
     * 检查分支是否干净
     * @param $code_path
     * @param $git
     * @return bool
     */
    public static function isBranchClean($code_path, $git)
    {
        $ret = Utils::runExec("cd $code_path && $git status");
        if (false === $ret) {
            throw new Exception("代码不存在git配置:$code_path");
        }
        $return_str = implode(' ', $ret);
        if (strpos($return_str, 'working tree clean') === false) {
            return false;
        }
        return true;
    }

    /**
     * 压缩代码
     * @param $zip_path 压缩文件存放路径
     * @param $code_path 要压缩的文件路径
     * @param $zip_file_name 压缩成的zip名称 xxx.zip
     * @return bool
     */
    public static function zipCode($zip_path, $code_path, $zip_file_name)
    {
        if (!file_exists($zip_path) || !file_exists($code_path)) {
            throw new Exception("文件不存在");
        }
        $deploy_config = app()->config->get('deploy');
        $zip = $deploy_config['local_zip_bin'];
        // 判断文件是否已存在（被其他任务执行过）
        if (!file_exists($zip_path . DS . $zip_file_name)) {
            return true;
        }
        // 过滤git及log文件，压缩到zip目录
        return Utils::runExec("cd $zip_path && $zip -r $zip_file_name $code_path --exclude \\*.log --exclude \*.git\*");
    }


    /**
     * 上传代码
     * @param $remote_code_release_path 服务端代码存放路径
     * @param $zip_file 要上传的zip文件
     * @param $server_host 服务器IP
     * @param $server_user 用户名
     * @return bool
     */
    public static function upZipCode($remote_code_release_path, $zip_file, $server_host, $server_user)
    {
        $deploy_config = app()->config->get('deploy');

        $identity_file_path = $deploy_config['identity_file_path'];
        // 要发版的服务器信息
        $remote_ssh = $server_user . '@' . $server_host;

        // 检查并创建代码目录
        $ret = Utils::runExec("ssh $remote_ssh '[ -d $remote_code_release_path ] && echo 1 || mkdir -p $remote_code_release_path'");
        if (false === $ret) {
            return false;
        }
        // 上传代码
        return Utils::runExec("scp -i $identity_file_path $zip_file $remote_ssh:$remote_code_release_path");
    }
}