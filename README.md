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