# 代码发布系统
- 基于SSH + Deploy

## 架构
- php5.6 + nginx + mysql

## 概念
- 服务器组
- 项目组
- 任务组
- 子任务

## 流程
1. 前台创建任务
2. 后台进程实时监控任务的产生，并处理任务
   
## 任务
1. 获取未开始的任务组
2. 执行组任务：
    - 各个子项目代码复制->切换分支/标签->替换文件->写入release日志
    - 项目代码整体打包

3. 执行子任务：
    - 上传至服务器
    - 解压并部署
    - 保留历史版本
    
## 命令
- zip / unzip
- find 
- scp
- dep
- mkdir / mv / cp / rm

## 初始化项目步骤
- 拷贝本地环境配置文件,并修改响应配置选项
    ```
    cp .env.sample.php .env.php
    ```
- 初始化数据库表
    - 创建库表
        ```
         mysql> source xxx/install/init.sql
        ```
    - 写入项目配置到project表 
    - 写入服务器配置到server表
- 初始化项目代码
     ``` 
     php run init_projects_code 
     ```
- 启动发布任务监听脚本
    ```
    php run launch_release_job
    ```
    

## 常见问题
- 新添加服务器后，发布代码时会上传失败，解决办法：
```
在本地系统通过ssh登录服务器一次，将服务器的指纹写入.ssh/know_hosts文件中
```